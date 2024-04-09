<?php

namespace components;
interface IComponent
{
    function getAttributes();
    function getImportStatement();
    function getImportsStatement();

    function getControllerVariables();// todo wat als er twee gelijke componenten in eenzelfde page zitten qua var => bv. items 1 en items2?
    function getConstructorInjections();
    function getControllerImports();


    function getInit($pages);

    function getHTML(string $triggers,\Action $action=null);
}