<?php
interface IComponent{
    function getAttributes();
    function getImportStatement();
    function getComponentImportStatements(int $levelsOfNesting);
    function getImportsStatement();
}