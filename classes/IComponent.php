<?php
interface IComponent{
    function getAttributes();
    function getImportStatement();
    function getComponentImportStatements(int $levelsOfNesting,array $pages);
    function getImportsStatement();
    function getVariables();// todo wat als er twee gelijke componenten in eenzelfde page zitten qua var => bv. items 1 en items2?
    function getInit($pages);
    function getConstructor();
    function getHTML();
}