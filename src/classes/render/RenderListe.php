<?php

/**
 * interface RenderListe
 * permet le rendu d'une série
 */
interface RenderListe {

    /** fonction render qui permet le rendu d'une série
     * @param Serie $serie la série à retourner
     * @return string le rendu de la série
     */
    public function render(Serie $serie) : string;

}
