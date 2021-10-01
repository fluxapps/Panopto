<?php

use srag\Plugins\Panopto\DTO\ContentObject;

/**
 * Class xpanTableGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class xpanSortingTableGUI extends ilTable2GUI
{

    const TBL_ROW_TEMPLATE_NAME = "tpl.sorting_row.html";
    const TBL_ROW_TEMPLATE_DIR = "/templates/table_rows/";
    const JS_FILES_TO_EMBED
        = [
            "/js/sortable.min.js?2",
        ];
    const CSS_FILES_TO_EMBED
        = [
            "/templates/default/sorting_table.css",
        ];


    /**
     * xpanTableGUI constructor.
     * @param                 $a_parent_obj
     * @param ilPanoptoPlugin $pl
     * @param ContentObject[] $content_objects
     */
    public function __construct($a_parent_obj, $pl, $content_objects)
    {
        parent::__construct($a_parent_obj);
        $plugin_dir = $pl->getDirectory();

        $this->initColumns($pl);
        $this->setRowTemplate($pl->getDirectory() . self::TBL_ROW_TEMPLATE_DIR . self::TBL_ROW_TEMPLATE_NAME, $plugin_dir);

        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setShowRowsSelector(true);

        $this->applyFiles($plugin_dir);
        $this->parseData($content_objects);
    }


    /**
     * @param ilPanoptoPlugin $pl
     */
    protected function initColumns($pl)
    {
        $this->addColumn("", 'move_icon');
        $this->addColumn($pl->txt('content_thumbnail'));
        $this->addColumn($pl->txt('content_title'));
        $this->addColumn($pl->txt('content_description'));
    }

    /**
     * @param ContentObject $content_object
     */
    protected function fillRow($content_object)
    {
        $this->tpl->setVariable("VAL_THUMBNAIL", $content_object->getThumbnailUrl());
        $this->tpl->setVariable("VAL_TITLE", $content_object->getTitle());
        $this->tpl->setVariable("VAL_DESCRIPTION", $content_object->getDescription());
        $this->tpl->setVariable("VAL_MID", $content_object->getId());
    }


    /**
     * @param string $plugin_dir
     */
    protected function applyFiles($plugin_dir)
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        foreach (self::JS_FILES_TO_EMBED as $pathSuffix) {
            $main_tpl->addJavaScript($plugin_dir . $pathSuffix);
        }

        foreach (self::CSS_FILES_TO_EMBED as $pathSuffix) {
            $main_tpl->addCss($plugin_dir . $pathSuffix);
        }

        $base_link = $this->ctrl->getLinkTarget($this->parent_obj, '', '', true);
        $main_tpl->addOnLoadCode('PanoptoSorter.init("' . $base_link . '");');
    }

    /**
     * @param ContentObject[] $content_objects
     */
    protected function parseData(array $content_objects)
    {
        $this->setData($content_objects);
    }
}
