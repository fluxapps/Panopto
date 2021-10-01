<#1>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/Panopto/classes/Settings/class.xpanSettings.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/Panopto/classes/Config/class.xpanConfig.php');
xpanSettings::updateDB();
xpanConfig::updateDB();
?>
<#2>
<?php
global $DIC;
$query = $DIC->database()->query('select * from lng_data where module = "rep_robj_xpan" and identifier = "rep_robj_xpan_obj_xpan"');
if (!$query->numRows()) {
    $DIC->database()->insert('lng_data', array(
        'module' => array('text', 'rep_robj_xpan'),
        'identifier' => array('text', 'rep_robj_xpan_obj_xpan'),
        'lang_key' => array('text', 'de'),
        'value' => array('text', 'Panopto Video')
    ));
    $DIC->database()->insert('lng_data', array(
        'module' => array('text', 'rep_robj_xpan'),
        'identifier' => array('text', 'rep_robj_xpan_obj_xpan'),
        'lang_key' => array('text', 'en'),
        'value' => array('text', 'Panopto Video')
    ));
}
$query = $DIC->database()->query('select * from lng_data where module = "rep_robj_xpan" and identifier = "rep_robj_xpan_objs_xpan"');
if (!$query->numRows()) {
    $DIC->database()->insert('lng_data', array(
        'module' => array('text', 'rep_robj_xpan'),
        'identifier' => array('text', 'rep_robj_xpan_objs_xpan'),
        'lang_key' => array('text', 'de'),
        'value' => array('text', 'Panopto Videos')
    ));
    $DIC->database()->insert('lng_data', array(
        'module' => array('text', 'rep_robj_xpan'),
        'identifier' => array('text', 'rep_robj_xpan_objs_xpan'),
        'lang_key' => array('text', 'en'),
        'value' => array('text', 'Panopto Videos')
    ));
}
?>
<#3>
<?php
SorterEntry::updateDB();
?>
<#4>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/Panopto/classes/Settings/class.xpanSettings.php');
xpanSettings::updateDB();
?>
