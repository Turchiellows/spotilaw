<?php
  class CamaraDeputados {
    protected $urls = [];
    protected $deputados = 0;
    protected $partidos = 0;
    protected $siglasProposicoes = 0;
    protected $situacoesProposicoes = 0;
    protected $tiposAutores = 0;
    protected $tiposOrgaos = 0;
    protected $orgaos = 0;
    protected $proposicoes = 0;
    protected $Connect;

    function __construct() {
  //     urls bando de dados livre das proposições da Câmara dos Deputados
      array_push($this->urls, "http://www.camara.leg.br/SitCamaraWS/Deputados.asmx/ObterDeputados");
      array_push($this->urls, "http://www.camara.leg.br/SitCamaraWS/Deputados.asmx/ObterPartidosCD");
      array_push($this->urls, "http://www.camara.leg.br/SitCamaraWS/Proposicoes.asmx/ListarSiglasTipoProposicao");
      array_push($this->urls, "http://www.camara.leg.br/SitCamaraWS/Proposicoes.asmx/ListarSituacoesProposicao");
      array_push($this->urls, "http://www.camara.leg.br/SitCamaraWS/Proposicoes.asmx/ListarTiposAutores");
      array_push($this->urls, "http://www.camara.leg.br/SitCamaraWS/Orgaos.asmx/ListarTiposOrgaos");
      array_push($this->urls, "http://www.camara.leg.br/SitCamaraWS/Orgaos.asmx/ObterOrgaos");
      array_push($this->urls, "http://www.camara.leg.br/SitCamaraWS/Proposicoes.asmx/ListarProposicoes");
    }

    function connecting() {
      if(!$this->Connect){
        $this->Connect = new Connect();

        if($this->Connect->query("CREATE DATABASE IF NOT EXISTS spotilaw")){

          $this->Connect->selectDatabase();

          //--- //
          $this->Connect->query("CREATE TABLE IF NOT EXISTS `deputados` (
            `ideCadastro` int(10) unsigned NOT NULL,
            `codOrcamento` int(10) NOT NULL,
            `condicao` varchar(100) NOT NULL,
            `matricula` int(10) NOT NULL,
            `idParlamentar` int(11) NOT NULL,
            `nome` varchar(50) NOT NULL,
            `nomeParlamentar` varchar(50) NOT NULL,
            `urlFoto` varchar(100) NOT NULL,
            `sexo` varchar(10) NOT NULL,
            `uf` varchar(2) NOT NULL,
            `partido` varchar(12) NOT NULL,
            `gabinete` int(10) NOT NULL,
            `anexo` int(10) NOT NULL,
            `fone` int(10) NOT NULL,
            `email` varchar(100) NOT NULL,
            `comissoes` varchar(100) NOT NULL,
            PRIMARY KEY (`ideCadastro`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

          $this->Connect->query("CREATE TABLE IF NOT EXISTS `partidos` (
            `idPartido` varchar(10) NOT NULL,
            `siglaPartido` varchar(20) NOT NULL,
            `nomePartido` varchar(100) NOT NULL,
            `dataCriacao` date NOT NULL,
            `dataExtincao` date NOT NULL,
            PRIMARY KEY (`idPartido`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

          $this->Connect->query("CREATE TABLE IF NOT EXISTS `siglasProposicoes` (
            `tipoSigla` varchar(10) NOT NULL,
            `descricao` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
            `ativa` tinyint(1) NOT NULL,
            `genero` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
            PRIMARY KEY (`tipoSigla`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

          $this->Connect->query("CREATE TABLE IF NOT EXISTS `situacoesProposicoes` (
            `id` int(10) unsigned NOT NULL,
            `descricao` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            `ativa` int(1) NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

          $this->Connect->query("CREATE TABLE IF NOT EXISTS `tiposAutores` (
            `id` int(10) unsigned NOT NULL,
            `descricao` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

          $this->Connect->query("CREATE TABLE IF NOT EXISTS `tiposOrgaos` (
            `id` int(10) unsigned NOT NULL,
            `descricao` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

          $this->Connect->query("CREATE TABLE IF NOT EXISTS `tiposOrgaos` (
            `id` int(10) unsigned NOT NULL,
            `descricao` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

          $this->Connect->query("CREATE TABLE IF NOT EXISTS `orgaos` (
            `id` int(10) unsigned NOT NULL,
            `idTipodeOrgao` int(10) NOT NULL,
            `sigla` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
            `descricao` text COLLATE utf8_unicode_ci NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");


          $this->Connect->query("CREATE TABLE IF NOT EXISTS `proposicoes` (
            `id` int(10) unsigned NOT NULL,
            `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
            `idTipoProposicao` int(10) NOT NULL,
            `numero` int(10) NOT NULL,
            `ano` int(4) NOT NULL,
            `idOrgaoNumerador` int(10) NOT NULL,
            `datApresentacao` date NOT NULL,
            `txtEmenta` text COLLATE utf8_unicode_ci NOT NULL,
            `txtExplicacaoEmenta` text COLLATE utf8_unicode_ci NOT NULL,
            `idRegime` int(10) NOT NULL,
            `idApreciacao` int(10) NOT NULL,
            `idAutor` int(10) NOT NULL,
            `qtdAutores` int(10) NOT NULL,
            `idUltimoDespacho` int(11) NOT NULL,
            `idSituacao` int(10) NOT NULL,
            `indGenero` int(10) NOT NULL,
            `qtdOrgaosComEstado` tinyint(1) NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

          // ---//
        }
      }
    }

  //   obter XML do Legislativo
    function getData($url) {
      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_FAILONERROR, 1);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 15);

      $sXML = curl_exec($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      $oXML = new SimpleXMLElement($sXML);
      return $oXML;
    }

  //   obter 8 tabela de dados
    function getDataAll() {
      $this->getDeputados();
      $this->getPartidos();
      $this->getSituacoesProposicoes();
      $this->getSiglasProposicoes();
      $this->getTiposAutores();
      $this->getTiposOrgaos();
      $this->getOrgaos();
      $this->getProposicoes(2018);
    }
    
  //   obter tabela dos Deputados
    function getDeputados() {
      $this->deputados = 0;
      $oXML = $this->getData($this->urls[0]);
      $this->deputados = $oXML->count();
      $this->updateDeputados($oXML);
    }

  //   obter tabela dos partidos
    function getPartidos() {
      $this->partidos = 0;
      $oXML = $this->getData($this->urls[1]);
      $this->partidos = $oXML->count();
      $this->updatePartidos($oXML);
    }

  //   obter tabela dos siglas de proposições
    function getSiglasProposicoes() {
      $this->siglasProposicoes = 0;
      $oXML = $this->getData($this->urls[2]);
      $this->siglasProposicoes = $oXML->count();
      $this->updateSiglasProposicoes($oXML);
    }

  //   obter tabela dos situações de proposições
    function getSituacoesProposicoes() {
      $this->situacoesProposicoes = 0;
      $oXML = $this->getData($this->urls[3]);
      $this->situacoesProposicoes = $oXML->count();
      $this->updateSituacoesProposicoes($oXML);
    }

  //   obter tabela dos tipos de autores
    function getTiposAutores() {
      $this->tiposAutores = 0;
      $oXML = $this->getData($this->urls[4]);
      $this->tiposAutores = $oXML->count();
      $this->updateTiposAutores($oXML);
    }

  //   obter tabela dos tipos de orgãos
    function getTiposOrgaos() {
      $this->tiposOrgaos = 0;
      $oXML = $this->getData($this->urls[4]);
      $this->tiposOrgaos = $oXML->count();
      $this->updateTiposOrgaos($oXML);
    }

  //   obter tabela de orgãos
    function getOrgaos() {
      $this->orgaos = 0;
      $oXML = $this->getData($this->urls[6]);
      $this->orgaos = $oXML->count();
      $this->updateOrgaos($oXML);
    }

  //   obter tabela dos proposições
    function getProposicoes($ano=0) {
      $this->proposicoes = 0;
      $oXML = $this->getData("{$this->urls[7]}?sigla=PL&numero=&ano={$ano}&datApresentacaoIni=&datApresentacaoFim=&parteNomeAutor=&idTipoAutor=&siglaPartidoAutor=&siglaUFAutor=&generoAutor=&codEstado=&codOrgaoEstado=&emTramitacao=");
      $this->proposicoes = $oXML->count();
      $this->updateProposicoes($oXML);
    }

    function getProposicoesSigla($ano=0) {
      $this->connecting();

      $this->proposicoes = 0;
      $result = $this->Connect->query("SELECT tipoSigla FROM siglasProposicoes");
      while($sigla = mysqli_fetch_assoc($result)){
        $sigla['tipoSigla'] = trim($sigla['tipoSigla']);

        $oXML = $this->getData("{$this->urls[7]}?sigla={$sigla['tipoSigla']}&numero=&ano={$ano}&datApresentacaoIni=&datApresentacaoFim=&parteNomeAutor=&idTipoAutor=&siglaPartidoAutor=&siglaUFAutor=&generoAutor=&codEstado=&codOrgaoEstado=&emTramitacao=");
        if($oXML->count() > 0) {
          $this->proposicoes += $oXML->count();
          $this->updateProposicoes($oXML);
        }else{
          echo "{$sigla['tipoSigla']} não tem nada <br/>";
        }
      }
    }

    function updateDeputados($oXML) {
      $this->connecting();

      foreach ($oXML as $o) {
        $o->nome = addslashes($o->nome);
        $o->nomeParlamentar = addslashes($o->nomeParlamentar);
        $o->urlFoto = addslashes($o->urlFoto);

        $this->Connect->query("INSERT INTO `deputados` (".
                                "`ideCadastro`,".
                                "`codOrcamento`,".
                                "`condicao`,".
                                "`matricula`,".
                                "`idParlamentar`,".
                                "`nome`,".
                                "`nomeParlamentar`,".
                                "`urlFoto`,".
                                "`sexo`,".
                                "`uf`,".
                                "`partido`,".
                                "`gabinete`,".
                                "`anexo`,".
                                "`fone`,".
                                "`email`) ".
                              "VALUES (".
                                "'{$o->ideCadastro}',".
                                "'{$o->codOrcamento}',".
                                "'{$o->condicao}',".
                                "'{$o->matricula}',".
                                "'{$o->idParlamentar}',".
                                "'{$o->nome}',".
                                "'{$o->nomeParlamentar}',".
                                "'{$o->urlFoto}',".
                                "'{$o->sexo}',".
                                "'{$o->uf}',".
                                "'{$o->partido}',".
                                "'{$o->gabinete}',".
                                "'{$o->anexo}',".
                                "'{$o->fone}',".
                                "'{$o->email}') ".
                              "ON DUPLICATE KEY UPDATE ".
                                "codOrcamento=VALUES(codOrcamento),".
                                "condicao=VALUES(condicao),".
                                "matricula=VALUES(matricula),".
                                "idParlamentar=VALUES(idParlamentar),".
                                "nome=VALUES(nome),".
                                "nomeParlamentar=VALUES(nomeParlamentar),".
                                "urlFoto=VALUES(urlFoto),".
                                "sexo=VALUES(sexo),".
                                "uf=VALUES(uf),".
                                "partido=VALUES(partido),".
                                "gabinete=VALUES(gabinete),".
                                "anexo=VALUES(anexo),".
                                "fone=VALUES(fone),".
                                "email=VALUES(email)");
      }
    }

    function updatePartidos($oXML) {
      $this->connecting();

      foreach ($oXML as $o) {
        $o->nomePartido = addslashes($o->nomePartido);
        $o->dataCriacao = (trim($o->dataCriacao) && strtotime($o->dataCriacao) > 0) ? date("Y-m-d",strtotime($o->dataCriacao)) : "";
        $o->dataExtincao = (trim($o->dataExtincao) && strtotime($o->dataExtincao) > 0) ? date("Y-m-d",strtotime($o->dataExtincao)) : "";

        $this->Connect->query("INSERT INTO `partidos` (".
                                "`idPartido`,".
                                "`siglaPartido`,".
                                "`nomePartido`,".
                                "`dataCriacao`,".
                                "`dataExtincao`) ".
                              "VALUES (".
                                "'{$o->idPartido}',".
                                "'{$o->siglaPartido}',".
                                "'{$o->nomePartido}',".
                                "'{$o->dataCriacao}',".
                                "'{$o->dataExtincao}') ".
                              "ON DUPLICATE KEY UPDATE ".
                                "siglaPartido=VALUES(siglaPartido),".
                                "nomePartido=VALUES(nomePartido),".
                                "dataCriacao=VALUES(dataCriacao),".
                                "dataExtincao=VALUES(dataExtincao)");
      }    
    }  

    function updateSiglasProposicoes($oXML) {
      $this->connecting();

      foreach ($oXML as $o) {
        $o['descricao'] = addslashes($o['descricao']);
        $o['ativa'] = ($o['ativa'] == 'True');

        $this->Connect->query("INSERT INTO `siglasProposicoes` (".
                                "`tipoSigla`,".
                                "`descricao`,".
                                "`ativa`,".
                                "`genero`) ".
                              "VALUES (".
                                "'{$o['tipoSigla']}',".
                                "'{$o['descricao']}',".
                                "'{$o['ativa']}',".
                                "'{$o['genero']}') ".
                              "ON DUPLICATE KEY UPDATE ".
                                "descricao=VALUES(descricao),".
                                "ativa=VALUES(ativa),".
                                "genero=VALUES(genero)");
      }    
    }

    function updateSituacoesProposicoes($oXML) {
      $this->connecting();

      foreach ($oXML as $o) {
        $o['descricao'] = addslashes($o['descricao']);
        $o['ativa'] = ($o['ativa'] == 'True');

        $this->Connect->query("INSERT INTO `situacoesProposicoes` (".
                                "`id`,".
                                "`descricao`,".
                                "`ativa`) ".
                              "VALUES (".
                                "'{$o['id']}',".
                                "'{$o['descricao']}',".
                                "'{$o['ativa']}') ".
                              "ON DUPLICATE KEY UPDATE ".
                                "descricao=VALUES(descricao),".
                                "ativa=VALUES(ativa)");
      }    
    }

    function updateTiposAutores($oXML) {
      $this->connecting();

      foreach ($oXML as $o) {
        $o['descricao'] = addslashes($o['descricao']);

        $this->Connect->query("INSERT INTO `tiposAutores` (".
                                "`id`,".
                                "`descricao`) ".
                              "VALUES (".
                                "'{$o['id']}',".
                                "'{$o['descricao']}') ".
                              "ON DUPLICATE KEY UPDATE ".
                                "descricao=VALUES(descricao)");
      }    
    }

    function updateTiposOrgaos($oXML) {
      $this->connecting();

      foreach ($oXML as $o) {
        $o['descricao'] = addslashes($o['descricao']);

        $this->Connect->query("INSERT INTO `tiposOrgaos` (".
                                "`id`,".
                                "`descricao`) ".
                              "VALUES (".
                                "'{$o['id']}',".
                                "'{$o['descricao']}') ".
                              "ON DUPLICATE KEY UPDATE ".
                                "descricao=VALUES(descricao)");
      }
    }

    function updateOrgaos($oXML) {
      $this->connecting();

      foreach ($oXML as $o) {
        $o['descricao'] = addslashes($o['descricao']);

        $this->Connect->query("INSERT INTO `orgaos` (".
                                "`id`,".
                                "`idTipodeOrgao`,".
                                "`sigla`,".
                                "`descricao`) ".
                              "VALUES (".
                                "'{$o['id']}',".
                                "'{$o['idTipodeOrgao']}', ".
                                "'{$o['sigla']}', ".
                                "'{$o['descricao']}') ".
                              "ON DUPLICATE KEY UPDATE ".
                                "idTipodeOrgao=VALUES(idTipodeOrgao),".
                                "sigla=VALUES(sigla),".
                                "descricao=VALUES(descricao)");
      }
    }

    function updateProposicoes($oXML) {
      $this->connecting();

      foreach ($oXML as $o) {
        $o->nome = addslashes($o->nome);
        $o->txtEmenta = addslashes($o->txtEmenta);
        $o->txtExplicacaoEmenta = addslashes($o->txtExplicacaoEmenta);

        $o->idTipoProposicao = $o->tipoProposicao->id;
        $o->idOrgaoNumerador = $o->orgaoNumerador->id;
        $o->idRegime = $o->regime->codRegime;
        $o->idApreciacao = $o->apreciacao->id;
        $o->idAutor = $o->autor1->id;
        $o->idSituacao = $o->situacao->id;

        $this->Connect->query("INSERT INTO `proposicoes` (".
                                "`id`,".
                                "`nome`,".
                                "`idTipoProposicao`,".
                                "`numero`,".
                                "`ano`,".
                                "`idOrgaoNumerador`,".
                                "`datApresentacao`,".
                                "`txtEmenta`,".
                                "`txtExplicacaoEmenta`,".
                                "`idRegime`,".
                                "`idApreciacao`,".
                                "`idAutor`,".
                                "`qtdAutores`,".
                                "`idSituacao`,".
                                "`indGenero`,".
                                "`qtdOrgaosComEstado`) ".
                              "VALUES (".
                                "'{$o->id}',".
                                "'{$o->nome}',".
                                "'{$o->idTipoProposicao}',".
                                "'{$o->numero}',".
                                "'{$o->ano}',".
                                "'{$o->idOrgaoNumerador}',".
                                "'{$o->datApresentacao}',".
                                "'{$o->txtEmenta}',".
                                "'{$o->txtExplicacaoEmenta}',".
                                "'{$o->idRegime}',".
                                "'{$o->idApreciacao}',".
                                "'{$o->idAutor}',".
                                "'{$o->qtdAutores}',".
                                "'{$o->idSituacao}',".
                                "'{$o->indGenero}',".
                                "'{$o->qtdOrgaosComEstado}') ".
                              "ON DUPLICATE KEY UPDATE ".
                                "nome=VALUES(nome),".
                                "idTipoProposicao=VALUES(idTipoProposicao),".
                                "numero=VALUES(numero),".
                                "ano=VALUES(ano),".
                                "idOrgaoNumerador=VALUES(idOrgaoNumerador),".
                                "datApresentacao=VALUES(datApresentacao),".
                                "txtEmenta=VALUES(txtEmenta),".
                                "txtExplicacaoEmenta=VALUES(txtExplicacaoEmenta),".
                                "idRegime=VALUES(idRegime),".
                                "idApreciacao=VALUES(idApreciacao),".
                                "idAutor=VALUES(idAutor),".
                                "qtdAutores=VALUES(qtdAutores),".
                                "idSituacao=VALUES(idSituacao),".
                                "indGenero=VALUES(indGenero),".
                                "qtdOrgaosComEstado=VALUES(qtdOrgaosComEstado)");
      }
    }

  //   retorna o total de registros obtidos na tabela Deputados
    function getTotalDeputados(){
      return $this->deputados;
    }

  //   retorna o total de registros obtidos na tabela Partidos  
    function getTotalPartidos(){
      return $this->partidos;
    }

  //   retorna o total de registros obtidos na tabela Siglas de Proposições  
    function getTotalSiglasProposicoes(){
      return $this->siglasProposicoes;
    }

    //   retorna o total de registros obtidos na tabela Tipos de Autores
    function getTotalTiposAutores(){
      return $this->tiposAutores;
    }

    //   retorna o total de registros obtidos na tabela Tipos de Orgaos
    function getTotalTiposOrgaos(){
      return $this->tiposOrgaos;
    }

    //   retorna o total de registros obtidos na tabela Orgaos
    function getTotalOrgaos(){
      return $this->orgaos;
    }

    //   retorna o total de registros obtidos na tabela Orgaos
    function getTotalProposicoes(){
      return $this->proposicoes;
    }
    
    function getStatistica(){
      $est  = '';
      $est .= '<!DOCTYPE html>';
      $est .= '<html xmlns="http://www.w3.org/1999/xhtml">';
      $est .= '<head>';
      $est .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
      $est .= '<title>Estatística</title>';
      $est .= '<style>';
      $est .= 'body, input, textarea {';
      $est .= '   font-family: "Fira Sans", "Source Sans Pro", Helvetica, Arial, sans-serif;';
      $est .= '   font-weight: 400;';
      $est .= '}';
      $est .= '</style>';
      $est .= '</head>';

      $est .= "<p><b>Estatística de obtenção de dados da Câmara dos Deputados</b></p>";
      $est .= "<p>&nbsp;</p>";
      $est .= "<p>&nbsp;Total Deputados: {$this->getTotalDeputados()}</p>";
      $est .= "<p>&nbsp;Total Partisos: {$this->getTotalPartidos()}</p>";
      $est .= "<p>&nbsp;Total Siglas Proposições: {$this->getTotalSiglasProposicoes()}</p>";
      $est .= "<p>&nbsp;Total Tipos Autores: {$this->getTotalTiposAutores()}</p>";
      $est .= "<p>&nbsp;Total Total Tipos Orgãos: {$this->getTotalTiposOrgaos()}</p>";
      $est .= "<p>&nbsp;Total Total Orgãos: {$this->getTotalOrgaos()}</p>";
      $est .= "<p>&nbsp;Total Total Proposicões: {$this->getTotalProposicoes()}</p>";
      
      $est .= '<body>';
      $est .= '</body>';
      $est .= '</html>';
      
      return $est;
    }

  }
?>