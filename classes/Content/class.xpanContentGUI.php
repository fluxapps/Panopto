<?php

use srag\Plugins\Panopto\DTO\ContentObject;

require_once __DIR__ . "/../../vendor/autoload.php";

/**
 * Class xpanContentGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xpanContentGUI: ilObjPanoptoGUI
 */
class xpanContentGUI extends xpanGUI {

    const CMD_SHOW = "index";
    const CMD_SORTING = "sorting";
    const TAB_SUB_SHOW = "subShow";
    const TAB_SUB_SORTING = "subSorting";

    /**
     * @var xpanClient
     */
    protected $client;
    /**
     * @var String
     */
    protected $folder_id;

    /**
     * xpanContentGUI constructor.
     * @param ilObjPanoptoGUI $parent_gui
     * @throws ilException
     */
    public function __construct(ilObjPanoptoGUI $parent_gui) {
        parent::__construct($parent_gui);

        $this->client = xpanClient::getInstance();

        $folder = $this->client->getFolderByExternalId($this->getObject()->getFolderExtId());
        if (!$folder) {
            throw new ilException('No external folder found for this object.');
        }

        $this->folder_id = $folder->getId();

        // grant user permissions on the fly
        if (!$this->client->hasUserViewerAccessOnFolder($this->folder_id)) {
            $this->client->grantUserAccessToFolder($this->folder_id, xpanClient::ROLE_VIEWER);
        }
    }

    /**
     * @throws Exception
     */
    protected function index() {
        $this->addSubTabs(self::TAB_SUB_SHOW);
        $content_objects = $this->client->getContentObjectsOfFolder(
            $this->folder_id,
            true,
            $_GET['xpan_page'],
            $this->getObject()->getReferenceId());
//        xpanRESTClient::getInstance()->getPlaylistsOfFolder($this->folder_id);
        if (!$content_objects['count']) {
            ilUtil::sendInfo($this->pl->txt('msg_no_videos'));
            return;
        }

        $tpl = new ilTemplate('tpl.content_list.html', true, true, $this->pl->getDirectory());
        $pages = 1 + floor($content_objects['count'] / 10);

        // "previous" button
        if ($_GET['xpan_page']) {
            $this->ctrl->setParameter($this, 'xpan_page', $_GET['xpan_page'] - 1);
            $link = $this->ctrl->getLinkTarget($this, self::CMD_STANDARD);
            // top
            $tpl->setCurrentBlock('previous_top');  // for some reason, i had to do 2 different blocks for top and bottom pagination
            $tpl->setVariable('LINK_PREVIOUS', $link);
            $tpl->parseCurrentBlock();
            // bottom
            $tpl->setCurrentBlock('previous_bottom');
            $tpl->setVariable('LINK_PREVIOUS', $link);
            $tpl->parseCurrentBlock();
        }

        // pages
        if ($pages > 1) {
            for ($i = 1; $i <= $pages; $i++) {
                $this->ctrl->setParameter($this, 'xpan_page', $i - 1);
                $link = $this->ctrl->getLinkTarget($this, self::CMD_STANDARD);
                // top
                $tpl->setCurrentBlock('page_top');
                $tpl->setVariable('LINK_PAGE', $link);
                if (($i-1) == $_GET['xpan_page']) {
                    $tpl->setVariable('ADDITIONAL_CLASS', 'xpan_page_active');
                }
                $tpl->setVariable('LABEL_PAGE', $i);
                $tpl->parseCurrentBlock();
                // bottom
                $tpl->setCurrentBlock('page_bottom');
                $tpl->setVariable('LINK_PAGE', $link);
                if (($i-1) == $_GET['xpan_page']) {
                    $tpl->setVariable('ADDITIONAL_CLASS', 'xpan_page_active');
                }
                $tpl->setVariable('LABEL_PAGE', $i);
                $tpl->parseCurrentBlock();
            }
        }

        // "next" button
        if ($content_objects['count'] > (($_GET['xpan_page'] + 1)*10)) {
            $this->ctrl->setParameter($this, 'xpan_page', $_GET['xpan_page'] + 1);
            $link = $this->ctrl->getLinkTarget($this, self::CMD_STANDARD);
            // top
            $tpl->setCurrentBlock('next_top');
            $tpl->setVariable('LINK_NEXT', $link);
            $tpl->parseCurrentBlock();
            // bottom
            $tpl->setCurrentBlock('next_bottom');
            $tpl->setVariable('LINK_NEXT', $link);
            $tpl->parseCurrentBlock();
        }

        // videos
        /** @var ContentObject $object */
        foreach ($content_objects['objects'] as $object) {
            if ($object instanceof \srag\Plugins\Panopto\DTO\Session) {
                $tpl->setCurrentBlock('duration');
                $tpl->setVariable('DURATION', $this->formatDuration($object->getDuration()));
                $tpl->parseCurrentBlock();
                $tpl->setVariable('IS_PLAYLIST', 'false');
            } else {
                $tpl->setVariable('IS_PLAYLIST', 'true');
                $tpl->touchBlock('playlist_icon');
            }

            $tpl->setCurrentBlock('list_item');
            $tpl->setVariable('ID', $object->getId());
            $tpl->setVariable('THUMBNAIL', $object->getThumbnailUrl());
            $tpl->setVariable('TITLE', $object->getTitle());
            $tpl->setVariable('DESCRIPTION', $object->getDescription());
            $tpl->parseCurrentBlock();
        }

        $this->tpl->addCss($this->pl->getDirectory() . '/templates/default/content_list.css?2');
        $this->tpl->addJavaScript($this->pl->getDirectory() . '/js/Panopto.js');
        $this->tpl->addOnLoadCode('Panopto.base_url = "https://' . xpanConfig::getConfig(xpanConfig::F_HOSTNAME) . '";');
        $this->tpl->setContent($tpl->get() . $this->getModalPlayer());
    }


    protected function sorting()
    {
        $this->addSubTabs(self::TAB_SUB_SORTING);

        $objects = $this->client->getContentObjectsOfFolder($this->folder_id, false, 0, $this->getObject()->getReferenceId());
        $sort_table_gui = new xpanSortingTableGUI($this, $this->pl, $objects);
        $this->tpl->setContent($sort_table_gui->getHTML());
    }


    /**
     * @param $duration_in_seconds
     * @return string
     */
    protected function formatDuration($duration_in_seconds) {
        $t = floor($duration_in_seconds);
        return sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60);
    }

    /**
     * @return String
     */
    protected function getModalPlayer() {
        $this->tpl->addCss($this->pl->getDirectory() . '/templates/default/modal.css');
        $modal = ilModalGUI::getInstance();
        $modal->setId('xpan_modal_player');
        $modal->setType(ilModalGUI::TYPE_LARGE);
//		$modal->setHeading('<div id="xoct_waiter_modal" class="xoct_waiter xoct_waiter_mini"></div>');
        $modal->setBody('<section><div id="xpan_video_container"></div></section>');
        return $modal->getHTML();
    }


    /**
     * Add sub tabs and activate the forwarded sub tab in the parameter.
     *
     * @param string $active_sub_tab
     */
    protected function addSubTabs($active_sub_tab)
    {
        global $DIC;

        $DIC->tabs()->addSubTab(self::TAB_SUB_SHOW,
            $this->pl->txt('content_show'),
            $DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW)
        );

        if ($DIC->access()->checkAccess("write", "", $this->parent_gui->ref_id)) {
            $DIC->tabs()->addSubTab(self::TAB_SUB_SORTING,
                $this->pl->txt('content_sorting'),
                $DIC->ctrl()->getLinkTarget($this, self::CMD_SORTING)
            );
        }

        $DIC->tabs()->activateSubTab($active_sub_tab);
    }


    /**
     * ajax
     */
    public function reorder()
    {
        global $DIC;
        $atom_query = new ilAtomQueryLock($DIC->database());
        $atom_query->addTableLock(SorterEntry::TABLE_NAME);
        $atom_query->addTableLock(SorterEntry::TABLE_NAME . '_seq');
        $atom_query->addQueryCallable(function(ilDBInterface $db) {
            $ids = $_POST['ids'];
            $precedence = 1;

            $existingEntries = SorterEntry::where(["ref_id" => $this->getObject()->getReferenceId()]);

            // Delete previous entries
            if ($existingEntries->hasSets()) {
                foreach ($existingEntries->get() as $entry) {
                    $entry->delete();
                }
            }

            foreach ($ids as $id) {
                $entry = new SorterEntry();
                $entry->setRefId($this->getObject()->getReferenceId());
                $entry->setPrecedence($precedence);
                $entry->setObjectId($id);
                $entry->create();
                $precedence++;
            }

            //echo "{\"success\": true}";
            //exit;
        });
        $atom_query->run();
    }

}
