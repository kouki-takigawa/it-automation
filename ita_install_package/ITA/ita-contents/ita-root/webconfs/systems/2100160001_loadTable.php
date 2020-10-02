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
/* ルートディレクトリの取得 */
if ( empty($root_dir_path) ){
    $root_dir_temp = array();
    $root_dir_temp = explode( "ita-root", dirname(__FILE__) );
    $root_dir_path = $root_dir_temp[0] . "ita-root";
}

require_once ( $root_dir_path . "/libs/webindividuallibs/systems/2100160001/validator.php");
$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
    global $g;

    $arrayWebSetting = array();
    $arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITACREPAR-MNU-102001");

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

    $table = new TableControlAgent('F_CREATE_MENU_INFO','CREATE_MENU_ID', $g['objMTS']->getSomeMessage("ITACREPAR-MNU-102002"), 'F_CREATE_MENU_INFO_JNL', $tmpAry);
    $tmpAryColumn = $table->getColumns();
    $tmpAryColumn['CREATE_MENU_ID']->setSequenceID('F_CREATE_MENU_INFO_RIC');
    $tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('F_CREATE_MENU_INFO_JSQ');
    unset($tmpAryColumn);


    // QMファイル名プレフィックス
    $table->setDBMainTableLabel( $g['objMTS']->getSomeMessage("ITACREPAR-MNU-102003"));
    // エクセルのシート名
    $table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile',  $g['objMTS']->getSomeMessage("ITACREPAR-MNU-102004"));

    //---- 検索機能の制御
    $table->setGeneObject('AutoSearchStart',false);  //('',true,false)
    // 検索機能の制御----


    // メニュー名
    $c = new TextColumn('MENU_NAME', $g['objMTS']->getSomeMessage("ITACREPAR-MNU-102005"));
    $c->setDescription( $g['objMTS']->getSomeMessage("ITACREPAR-MNU-102006"));//エクセル・ヘッダでの説明
    $c->getOutputType('filter_table')->setTextTagLastAttr('style = "ime-mode :active"');
    $c->getOutputType('register_table')->setTextTagLastAttr('style = "ime-mode :active"');
    $c->getOutputType('update_table')->setTextTagLastAttr('style = "ime-mode :active"');
    $objVldt = new MenuNameValidator(1,256,false);
    $c->setValidator($objVldt);
    $c->setRequired(true);//登録/更新時には、入力必須
    $c->setUnique(true);//登録/更新時には、DB上ユニークな入力であること必須
    $table->addColumn($c);
    
    // GUIメニューへのリンク
    $c = new LinkButtonColumn('GUI_detail_show', $g['objMTS']->getSomeMessage("ITACREPAR-MNU-104232"), $g['objMTS']->getSomeMessage("ITACREPAR-MNU-104232"), 'jumpToGui', array(':CREATE_MENU_ID')); 
    $table->addColumn($c);

    // 作成対象 
    $c = new IDColumn('TARGET',$g['objMTS']->getSomeMessage("ITACREPAR-MNU-102023"),'F_PARAM_TARGET','TARGET_ID','TARGET_NAME', '', array('ORDER'=>'ORDER BY DISP_SEQ'));
    $c->setDescription($g['objMTS']->getSomeMessage("ITACREPAR-MNU-102026"));//エクセル・ヘッダでの説明
    $c->setRequired(true);//登録/更新時には、入力必須
    $objVldt = new SubstitutionValidator($c);
    $c->setValidator($objVldt);
    $table->addColumn($c);

    // 表示順序
    $c = new NumColumn('DISP_SEQ',  $g['objMTS']->getSomeMessage("ITACREPAR-MNU-102007"));
    $c->setDescription( $g['objMTS']->getSomeMessage("ITACREPAR-MNU-102008"));
    $c->getOutputType('filter_table')->setTextTagLastAttr('style = "ime-mode :inactive"');
    $c->getOutputType('register_table')->setTextTagLastAttr('style = "ime-mode :inactive"');
    $c->getOutputType('update_table')->setTextTagLastAttr('style = "ime-mode :inactive"');
    $c->setSubtotalFlag(false);
    $c->setRequired(true);//登録/更新時には、入力必須
    $table->addColumn($c);

    // 用途
    $c = new IDColumn('PURPOSE',$g['objMTS']->getSomeMessage("ITACREPAR-MNU-102009"),'F_PARAM_PURPOSE','PURPOSE_ID','PURPOSE_NAME', '', array('OrderByThirdColumn'=>'PURPOSE_ID'));
    $c->setDescription($g['objMTS']->getSomeMessage("ITACREPAR-MNU-102010"));//エクセル・ヘッダでの説明
    $c->setRequired(false);
    $objVldt = new PurposeValidator($c);
    $c->setValidator($objVldt);
    $table->addColumn($c);

    // 縦メニュー利用
    $c = new IDColumn('VERTICAL',$g['objMTS']->getSomeMessage("ITACREPAR-MNU-102019"),'D_FLAG_LIST_01','FLAG_ID','FLAG_NAME','');
    $c->setDescription($g['objMTS']->getSomeMessage("ITACREPAR-MNU-102020"));//エクセル・ヘッダでの説明
    $objVldt = new VerticalValidator($c);
    $c->setValidator($objVldt);
    $table->addColumn($c);

    // 入力用メニューグループ
    $c = new IDColumn('MENUGROUP_FOR_INPUT',$g['objMTS']->getSomeMessage("ITACREPAR-MNU-102011"),'D_CMDB_MENU_GRP_LIST','MENU_GROUP_ID','MENU_GROUP_NAME','');
    $c->setDescription($g['objMTS']->getSomeMessage("ITACREPAR-MNU-102012"));//エクセル・ヘッダでの説明
    $c->setRequired(true);//登録/更新時には、入力必須
    $table->addColumn($c);

    // 代入値自動登録用メニューグループ
    $c = new IDColumn('MENUGROUP_FOR_SUBST',$g['objMTS']->getSomeMessage("ITACREPAR-MNU-102013"),'D_CMDB_MENU_GRP_LIST','MENU_GROUP_ID','MENU_GROUP_NAME','');
    $c->setDescription($g['objMTS']->getSomeMessage("ITACREPAR-MNU-102014"));//エクセル・ヘッダでの説明
    $c->setRequired(false);
    $objVldt = new MgForSubstValidator($c);
    $c->setValidator($objVldt);
    $table->addColumn($c);

    // 参照用メニューグループ
    $c = new IDColumn('MENUGROUP_FOR_VIEW',$g['objMTS']->getSomeMessage("ITACREPAR-MNU-102015"),'D_CMDB_MENU_GRP_LIST','MENU_GROUP_ID','MENU_GROUP_NAME','');
    $c->setDescription($g['objMTS']->getSomeMessage("ITACREPAR-MNU-102016"));//エクセル・ヘッダでの説明
    $c->setRequired(false);
    $objVldt = new MgForViewValidator($c);
    $c->setValidator($objVldt);
    $table->addColumn($c);

    // 説明
    $objVldt = new MultiTextValidator(0,1024,false);
    $c = new MultiTextColumn('DESCRIPTION', $g['objMTS']->getSomeMessage("ITACREPAR-MNU-102017"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITACREPAR-MNU-102018"));//エクセル・ヘッダでの説明
    $c->getOutputType('filter_table')->setTextTagLastAttr('style = "ime-mode :active"');
    $c->getOutputType('register_table')->setTextTagLastAttr('style = "ime-mode :active"');
    $c->getOutputType('update_table')->setTextTagLastAttr('style = "ime-mode :active"');
    $c->setValidator($objVldt);
    $table->addColumn($c);



//----head of setting [multi-set-unique]

//tail of setting [multi-set-unique]----


    $table->fixColumn();

    $table->setGeneObject('webSetting', $arrayWebSetting);
    return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);
unset($tmpFx);
?>
