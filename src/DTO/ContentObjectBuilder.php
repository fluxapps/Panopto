<?php

namespace srag\Plugins\Panopto\DTO;

use xpanConfig;

/**
 * Class ContentObjectBuilder
 * @package srag\Plugins\Panopto\DTO
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ContentObjectBuilder
{

    /**
     * @param array $results
     * @return Playlist[]
     */
    public static function buildPlaylistDTOsFromArray(array $results) : array
    {
        $playlists = [];
        foreach ($results as $result) {
            $playlists[] = new Playlist($result['Id'], $result['Name'], $result['Description'], $result['Urls']['ThumbnailUrl']);
        }
        return $playlists;
    }

    /**
     * @param \Panopto\SessionManagement\Session[] $sessions
     * @return Session[]
     */
    public static function buildSessionsDTOsFromSessions(array $sessions) : array
    {
        $sessions_array = [];
        foreach ($sessions as $session) {
            $sessions_array[] = new Session(
                $session->getId(),
                $session->getName(),
                $session->getDescription() ?? '',
                'https://' . xpanConfig::getConfig(xpanConfig::F_HOSTNAME) . $session->getThumbUrl(),
                $session->getDuration());
        }
        return $sessions_array;
    }
}
