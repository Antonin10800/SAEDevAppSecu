<?php

/**
 * interface EpisodeRender
 * permet le rendu d'un épisode
 */
interface EpisodeRender {

    /** fonction render qui permet le rendu d'un épisode
     * @param Episode $episode l'épisode à retourner
     * @return void
     */
    public function render(Episode $episode) : void;

}