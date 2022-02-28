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
//////////////////////////////////////////////////////////////////////
//
//  【処理概要】
//    ・WebDBCore機能を用いたWebページの中核設定を行う。
//
//////////////////////////////////////////////////////////////////////

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
    global $g;

    $arrayWebSetting = array();
    $arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-408030");
/*
Ansible(Pioneer)作業パターン
*/

    // ANSIBLEインターフェース情報の実行エンジンを取得
    $root_dir_path = $g['root_dir_path'];
    require_once ($root_dir_path . "/libs/backyardlibs/common/common_db_access.php");
    require_once ($root_dir_path . "/libs/backyardlibs/ansible_driver/ky_ansible_common_setenv.php");
    $dbobj = new CommonDBAccessCoreClass($g['db_model_ch'],$g['objDBCA'],$g['objMTS'],$g['login_id']);

    $sqlBody   = "select ANSIBLE_EXEC_MODE from B_ANSIBLE_IF_INFO where DISUSE_FLAG='0'";
    $arrayBind = array();
    $objQuery  = "";
    $ansible_exec_mode = 0;
    $ret = $dbobj->dbaccessExecute($sqlBody, $arrayBind, $objQuery);
    if($ret === false) {
        web_log($dbobj->GetLastErrorMsg());
    } else {
        if($objQuery->effectedRowCount() == 0) {
            $message = sprintf("Recode not found. (Table:B_ANSIBLE_IF_INFO");
            web_log('[FILE]' .basename(__FILE__) .'[LINE]' .__LINE__ .$message);
        } else {
            $row = $objQuery->resultFetch();
            // ANSIBLE_EXEC_MODE=2 ansible tower
            $ansible_exec_mode = $row['ANSIBLE_EXEC_MODE'];
        }
    }

    $tmpAry = array(
        'TT_SYS_01_JNL_SEQ_ID'=>'JOURNAL_SEQ_NO',
        'TT_SYS_02_JNL_TIME_ID'=>'JOURNAL_REG_DATETIME',
        'TT_SYS_03_JNL_CLASS_ID'=>'JOURNAL_ACTION_CLASS',
        'TT_SYS_04_NOTE_ID'=>'NOTE',
        'TT_SYS_04_DISUSE_FLAG_ID'=>'DISUSE_FLAG',
        'TT_SYS_05_LUP_TIME_ID'=>'LAST_UPDATE_TIMESTAMP',
        'TT_SYS_06_LUP_USER_ID'=>'LAST_UPDATE_USER',
        'TT_SYS_NDB_ROW_EDIT_BY_FILE_ID'=>'ROW_EDIT_BY_FILE',
        'TT_SYS_NDB_UPDATE_ID'=>'WEB_BUTTON_UPDATE',
        'TT_SYS_NDB_LUP_TIME_ID'=>'UPD_UPDATE_TIMESTAMP'
    );

    $table = new TableControlAgent('E_ANSIBLE_PNS_PATTERN','PATTERN_ID', $g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-408040"), 'E_ANSIBLE_PNS_PATTERN_JNL', $tmpAry);
    $tmpAryColumn = $table->getColumns();
    $tmpAryColumn['PATTERN_ID']->setSequenceID('C_PATTERN_PER_ORCH_RIC');
    $tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('C_PATTERN_PER_ORCH_JSQ');
    unset($tmpAryColumn);

    // ----VIEWをコンテンツソースにする場合、構成する実体テーブルを更新するための設定
    $table->setDBMainTableHiddenID('C_PATTERN_PER_ORCH');
    $table->setDBJournalTableHiddenID('C_PATTERN_PER_ORCH_JNL');
    // 利用時は、更新対象カラムに、「$c->setHiddenMainTableColumn(true);」を付加すること
    // VIEWをコンテンツソースにする場合、構成する実体テーブルを更新するための設定----

    //----作業実行で必要なのでtrueに
    $table->setJsEventNamePrefix(true);

    // QMファイル名プレフィックス
    $table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-408050"));
    // エクセルのシート名
    $table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile', $g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-408060"));

    $table->setAccessAuth(true);    // データごとのRBAC設定


    $table->addUniqueColumnSet(array('ITA_EXT_STM_ID','PATTERN_NAME'));

	$objVldt = new SingleTextValidator(1,256,false);
    $c = new TextColumn('PATTERN_NAME',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-408070"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-408080"));
    $c->setHiddenMainTableColumn(true);//コンテンツのソースがヴューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c->setValidator($objVldt);
    $c->setRequired(true);//登録/更新時には、入力必須
    $c->setUnique(true);//登録/更新時には、DB上ユニークな入力であること必須
    $table->addColumn($c);

    $tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
		$boolRet = true;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$strErrorBuf = "";

		$modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
		if( $modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate" ){
			$exeQueryData[$objColumn->getID()] = 4;
		}else if( $modeValue=="DTUP_singleRecDelete" ){
			$exeQueryData[$objColumn->getID()] = 4;
		}
		$retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
		return $retArray;
    };

    $c = new IDColumn('ITA_EXT_STM_ID',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-408090"),'B_ITA_EXT_STM_MASTER','ITA_EXT_STM_ID','ITA_EXT_STM_NAME','B_ITA_EXT_STM_MASTER');
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-409010"));//エクセル・ヘッダでの説明
    $c->setHiddenMainTableColumn(true);//コンテンツのソースがヴューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
    $c->setAllowSendFromFile(false);//エクセル/CSVからのアップロードを禁止する。
    $c->getOutputType('update_table')->setVisible(false);
    $c->getOutputType('register_table')->setVisible(false);
    $c->setJournalTableOfMaster('B_ITA_EXT_STM_MASTER_JNL');
    $c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
    $c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
    $c->setJournalKeyIDOfMaster('ITA_EXT_STM_ID');
    $c->setJournalDispIDOfMaster('ITA_EXT_STM_NAME');
    $c->setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);
    $table->addColumn($c);

    $c = new NumColumn('TIME_LIMIT',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-409020"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-409030"));//エクセル・ヘッダでの説明
    $c->setHiddenMainTableColumn(true);//コンテンツのソースがヴューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
    $c->setSubtotalFlag(false);
	$c->setValidator(new IntNumValidator(null,null));
    $table->addColumn($c);

    $cg = new ColumnGroup( $g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-409035") );

        $c = new IDColumn('ANS_HOST_DESIGNATE_TYPE_ID',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-409040"),'B_HOST_DESIGNATE_TYPE_LIST','HOST_DESIGNATE_TYPE_ID','HOST_DESIGNATE_TYPE_NAME','');
        $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-409050"));//エクセル・ヘッダでの説明
        $c->setHiddenMainTableColumn(true);//コンテンツのソースがヴューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
        $c->setJournalTableOfMaster('B_HOST_DESIGNATE_TYPE_LIST_JNL');
        $c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
        $c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
        $c->setJournalKeyIDOfMaster('HOST_DESIGNATE_TYPE_ID');
        $c->setJournalDispIDOfMaster('HOST_DESIGNATE_TYPE_NAME');
        $c->setRequired(true);//登録/更新時には、入力必須
        $cg->addColumn($c);

        // 並列実行数
        $c = new NumColumn('ANS_PARALLEL_EXE',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-409051"));
        $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-409052"));//エクセル・ヘッダでの説明
        $c->setHiddenMainTableColumn(true);//コンテンツのソースがヴューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
        $c->setSubtotalFlag(false);
        $c->setValidator(new IntNumValidator(0,null));
        $cg->addColumn($c);

    $table->addColumn($cg);

    // ANSIBLEインターフェース情報の実行エンジンがAnsible Engineの場合に利Ansible Engine用情報を表示
    if($ansible_exec_mode == DF_EXEC_MODE_ANSIBLE) {

        // Ansible Engine利用情報
        $cg = new ColumnGroup( $g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-9010000040") );

            /* Ansible virtualenv path*/
            $objVldt = new SingleTextValidator(0,512,false);
            $c = new TextColumn('ANS_ENGINE_VIRTUALENV_NAME',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-9010000027"));
            $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-9010000028"));
            $c->setHiddenMainTableColumn(true);
            $c->setValidator($objVldt);
            $c->setRequired(false);
            $cg->addColumn($c);

        $table->addColumn($cg);
    }

    // ANSIBLEインターフェース情報の実行エンジンがTowerの場合にTower利用情報を表示
    if($ansible_exec_mode == DF_EXEC_MODE_TOWER) {

        // Tower利用情報
        $cg = new ColumnGroup( $g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-9010000013") );

        // virtualenv
        $c = new IDColumn('ANS_VIRTUALENV_NAME',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-9010000029"),'B_ANS_TWR_VIRTUALENV','VIRTUALENV_NAME','VIRTUALENV_NAME','');
        $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-9010000030"));
        $c->setHiddenMainTableColumn(true);
        $objOT = new TraceOutputType(new ReqTabHFmt(), new TextTabBFmt());
        $objOT->setFirstSearchValueOwnerColumnID('ANS_VIRTUALENV_NAME');
        $aryTraceQuery = array(array('TRACE_TARGET_TABLE'=>'B_ANS_TWR_VIRTUALENV_JNL',
            'TTT_SEARCH_KEY_COLUMN_ID'=>'VIRTUALENV_NAME',
            'TTT_GET_TARGET_COLUMN_ID'=>'VIRTUALENV_NAME',
            'TTT_JOURNAL_SEQ_NO'=>'JOURNAL_SEQ_NO',
            'TTT_TIMESTAMP_COLUMN_ID'=>'LAST_UPDATE_TIMESTAMP',
            'TTT_DISUSE_FLAG_COLUMN_ID'=>'DISUSE_FLAG'
            )
        );
        $objOT->setTraceQuery($aryTraceQuery);
        $c->setOutputType('print_journal_table',$objOT);
        $cg->addColumn($c);

        $table->addColumn($cg);
    }

    // ANSIBLEインターフェース情報の実行エンジンがansible automation controllerの場合にansible automation controller利用情報を表示
    if($ansible_exec_mode == DF_EXEC_MODE_AAC) {

        // ansible automation controller利用情報
        $cg = new ColumnGroup( $g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-9010000035") );

            // 実行環境
            $c = new IDColumn('ANS_EXECUTION_ENVIRONMENT_NAME',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-9010000036"),'B_ANS_TWR_EXECUTION_ENVIRONMENT','EXECUTION_ENVIRONMENT_NAME','EXECUTION_ENVIRONMENT_NAME','');
            $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-9010000037")); //エクセル・ヘッダでの説明
            $c->setHiddenMainTableColumn(true); //コンテンツのソースがヴューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
            $objOT = new TraceOutputType(new ReqTabHFmt(), new TextTabBFmt());
            $objOT->setFirstSearchValueOwnerColumnID('ANS_EXECUTION_ENVIRONMENT_NAME');
            $aryTraceQuery = array(array('TRACE_TARGET_TABLE'=>'B_ANS_TWR_EXECUTION_ENVIRONMENT_JNL',
                    'TTT_SEARCH_KEY_COLUMN_ID'=>'EXECUTION_ENVIRONMENT_NAME',
                    'TTT_GET_TARGET_COLUMN_ID'=>'EXECUTION_ENVIRONMENT_NAME',
                    'TTT_JOURNAL_SEQ_NO'=>'JOURNAL_SEQ_NO',
                    'TTT_TIMESTAMP_COLUMN_ID'=>'LAST_UPDATE_TIMESTAMP',
                    'TTT_DISUSE_FLAG_COLUMN_ID'=>'DISUSE_FLAG'
                    )
            );
            $objOT->setTraceQuery($aryTraceQuery);
            $c->setOutputType('print_journal_table',$objOT);
            $cg->addColumn($c);

        $table->addColumn($cg);
    }

    $c = new FileUploadColumn('ANS_ANSIBLE_CONFIG_FILE',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-9010000038"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-9010000039"));//エクセル・ヘッダでの説明
    $c->setMaxFileSize(1024*1024);//単位はバイト
    $c->setFileHideMode(true);
    $c->setAllowUploadColmnSendRestApi(true);   //REST APIからのアップロード可否。FileUploadColumnのみ有効(default:false)
    $c->setAllowSendFromFile(false);            //エクセル/CSVからのアップロードを禁止する。
    $c->setRequired(false);                     //登録/更新時には、入力任意
    $c->setHiddenMainTableColumn(true);         //コンテンツのソースがViewなので、true設定
    $c->setNRPathAnyToBranchPerFUC('/uploadfiles/2100000305/ANS_ANSIBLE_CONFIG_FILE');
    $table->addColumn($c);

    // Movement詳細へのリンクボタン
    $strLabelText = $g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1207315");
    $c = new LinkButtonColumn('ethWakeOrder',$strLabelText, $strLabelText, 'dummy');
    $c->setDBColumn(false);
    $c->setEvent("print_table", "onClick", "newOpenWindow", array(':PATTERN_NAME'));
    $c->getOutputType('print_journal_table')->setVisible(false);

    $table->addColumn($c);

    $tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
        $boolRet = true;
        $intErrorType = null;
        $aryErrMsgBody = array();
        $strErrMsg = "";
        $strErrorBuf = "";
        $strFxName = "";

        $modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
        if( $modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate" || $modeValue=="DTUP_singleRecDelete" ){

            $strQuery = "UPDATE A_PROC_LOADED_LIST "
                       ."SET LOADED_FLG='0' ,LAST_UPDATE_TIMESTAMP = NOW(6) "
                       ."WHERE ROW_ID in (2100020004) ";

            $aryForBind = array();

            $aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);

            if( $aryRetBody[0] !== true ){
                $boolRet = false;
                $strErrMsg = $aryRetBody[2];
                $intErrorType = 500;
            }
        }
        $retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
        return $retArray;
    };
    $tmpAryColumn = $table->getColumns();
    $tmpAryColumn['PATTERN_ID']->setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);

    $table->fixColumn();
    $tmpAryColumn = $table->getColumns();
    list($strTmpValue,$tmpKeyExists) = isSetInArrayNestThenAssign($aryVariant,array('callType'),null);
    if( $tmpKeyExists===true ){
        if( $strTmpValue=="insConstruct" ){
            $objRadioColumn = $tmpAryColumn['WEB_BUTTON_UPDATE'];
            $objRadioColumn->setColLabel($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-409080"));

            $objFunctionB = function ($objOutputType, $rowData, $aryVariant, $objColumn){
                $strInitedColId = $objColumn->getID();

                $aryVariant['callerClass'] = get_class($objOutputType);
                $aryVariant['callerVars'] = array('initedColumnID'=>$strInitedColId,'free'=>null);
                $strRIColId = $objColumn->getTable()->getRIColumnID();

                $rowData[$strInitedColId] = '<input type="radio" name="patternNo" onclick="javascript:patternLoadForExecute(' . $rowData[$strRIColId] . ')"/>';

                return $objOutputType->getBody()->getData($rowData,$aryVariant);
            };

            $objTTBF = new TextTabBFmt();
            $objTTHF = new TabHFmt();//new SortedTabHFmt();
            $objTTBF->setSafingHtmlBeforePrintAgent(false);
            $objOutputType = new VariantOutputType($objTTHF, $objTTBF);
            $objOutputType->setFunctionForGetBodyTag($objFunctionB);
            $objOutputType->setVisible(true);
            $objRadioColumn->setOutputType("print_table", $objOutputType);

            $table->getFormatter('print_table')->setGeneValue("linkExcelHidden",true);
            $table->getFormatter('print_table')->setGeneValue("linkCSVFormShow",false);

            $tmpAryColumn['ethWakeOrder']->getOutputType('print_table')->setVisible(false);
        }
    }
    unset($tmpAryColumn);

    $table->setGeneObject('webSetting', $arrayWebSetting);
    return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);
unset($tmpFx);
?>
