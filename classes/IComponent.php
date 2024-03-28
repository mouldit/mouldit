<?php
interface IComponent{
    function getAttributes();
    function getImportStatement();
    function getComponentImportStatements(int $levelsOfNesting,array $pages);
    function getImportsStatement();
}