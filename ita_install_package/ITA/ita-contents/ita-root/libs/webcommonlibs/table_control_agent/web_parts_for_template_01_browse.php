<?php
//   Copyright 2019 NEC Corporation
//
//   Licensed under the Apache License, Version 2.0 (the "License");
//   you may not use this file except in compliance with the License.
//   You may obtain a copy of the License at
//
//       http://www.apache.org/licenses/LICENSE-2.0
//
//   Unless required by applicable law or agreed to in writing, software
//   distributed under the License is distributed on an "AS IS" BASIS,
//   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//   See the License for the specific language governing permissions and
//   limitations under the License.
//

    global $g;
    // ルートディレクトリを取得
    $tmpAry=explode('ita-root', dirname(__FILE__));$g['root_dir_path']=$tmpAry[0].'ita-root';unset($tmpAry);
    if(array_key_exists('no', $_GET)){
        $g['page_dir']  = $_GET['no'];
    }

    $param = explode ( "?" , $_SERVER["REQUEST_URI"] , 2 );
    if(count($param) == 2){
        $url_add_param = "&" . $param[1];
    }
    else{
        $url_add_param = "";
    }

    // DBアクセスを伴う処理を開始
    try{
        //----ここから01_系から06_系全て共通
        // DBコネクト
        require_once ( $g['root_dir_path'] . "/libs/commonlibs/common_php_req_gate.php");
        // 共通設定取得パーツ
        require_once ( $g['root_dir_path'] . "/libs/webcommonlibs/web_parts_get_sysconfig.php");
        // メニュー情報取得パーツ
        require_once ( $g['root_dir_path'] . "/libs/webcommonlibs/web_parts_menu_info.php");
        //ここまで01_系から06_系全て共通----

        // browse系共通ロジックパーツ01
        require_once ( $g['root_dir_path'] . "/libs/webcommonlibs/web_parts_for_browse_01.php");
    }
    catch (Exception $e){
        // DBアクセス例外処理パーツ
        require_once ( $g['root_dir_path'] . "/libs/webcommonlibs/web_parts_db_access_exception.php");
    }

    //----デフォルトのロードテーブル関数をコレクション
    $systemFile = "{$g['root_dir_path']}/webconfs/systems/{$g['page_dir']}_loadTable.php";
    $sheetFile = "{$g['root_dir_path']}/webconfs/sheets/{$g['page_dir']}_loadTable.php";
    $userFile = "{$g['root_dir_path']}/webconfs/users/{$g['page_dir']}_loadTable.php";
    if(file_exists($systemFile)){
        require_once($systemFile);
    }
    else if(file_exists($sheetFile)){
        require_once($sheetFile);
    }
    else if(file_exists($userFile)){
        require_once($userFile);
    }
    $retArray = getTCAConfig();

    $objDefaultTable = $retArray['objTable'];
    $privilege = $retArray['privilege'];
    $pageType = $retArray['pageType'];
    $strPageInfo = $retArray['PageInfoArea'];
    $strDeveloperArea = $retArray['DeveloperArea'];
    $strHtmlFilter1Commnad = $retArray['FilterCmdArea'];
    $strHtmlFilter2Commnad = $retArray['RegisterFilterArea'];
    $boolShowRegisterArea = $retArray['RegisterAreaShow'];
    $strHtmlFileEditCommnad = $retArray['QMFileAreaCmd'];
    $strTemplateBody = $retArray['JscriptTmpl'];
    $strHtmlJnlFilterCommnad = $retArray['JnlSearchFilterCmdArea'];

    $varWebRowLimit = $retArray['WebPrintRowLimit'];
    $varWebRowConfirm = $retArray['WebPrintRowConfirm'];

    $intTableWidth = $retArray['WebStdTableWidth'];
    $intTableHeight = $retArray['WebStdTableHeight'];

    $strCmdWordAreaOpen = $g['objMTS']->getSomeMessage("ITAWDCH-STD-251");
    $strCmdWordAreaClose = $g['objMTS']->getSomeMessage("ITAWDCH-STD-252");

    // 共通HTMLステートメントパーツ
    require_once ( $g['root_dir_path'] . "/libs/webcommonlibs/web_parts_html_statement.php");

    $jsSystemFile = "/menus/systems/{$g['page_dir']}/00_javascript.js";
    $jsSheetFile = "/menus/sheets/{$g['page_dir']}/00_javascript.js";
    $jsUserFile = "/menus/users/{$g['page_dir']}/00_javascript.js";
    if(file_exists("{$g['root_dir_path']}/webroot" . $jsSystemFile)){
        $jsFile = "{$g['scheme_n_authority']}" . $jsSystemFile;
        $jsFile_Absolute_path = "{$g['root_dir_path']}/webroot" . $jsSystemFile;
    }
    else if(file_exists("{$g['root_dir_path']}/webroot" . $jsSheetFile)){
        $jsFile = "{$g['scheme_n_authority']}" . $jsSheetFile;
        $jsFile_Absolute_path = "{$g['root_dir_path']}/webroot" . $jsSheetFile;
    }
    else if(file_exists("{$g['root_dir_path']}/webroot" . $jsUserFile)){
        $jsFile = "{$g['scheme_n_authority']}" . $jsUserFile;
        $jsFile_Absolute_path = "{$g['root_dir_path']}/webroot" . $jsUserFile;
    }
    else{
        $jsFile = "{$g['scheme_n_authority']}/default/menu/00_javascript.js";
        $jsFile_Absolute_path = "{$g['root_dir_path']}/webroot/default/menu/00_javascript.js";
    }

    // javascript,css更新時自動で読込みなおす為にファイルのタイムスタンプをパラメーターに持つ
    $timeStamp_00_javascript_js = filemtime("$jsFile_Absolute_path");

    print 
<<< EOD
    <script type="text/javascript" src="/default/menu/02_access.php?client=all$url_add_param"></script>
    <script type="text/javascript" src="/default/menu/02_access.php?stub=all$url_add_param"></script>
    <script type="text/javascript" src="{$jsFile}?$timeStamp_00_javascript_js"></script>
EOD;

    // browse系共通ロジックパーツ02
    require_once ( $root_dir_path . "/libs/webcommonlibs/web_parts_for_browse_02.php");
?>
