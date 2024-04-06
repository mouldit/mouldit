<?php

namespace components;
interface IComponent
{
    function getAttributes();

    function getImportStatement();
    function getImportsStatement();
    function getComponentImportStatements();
    function getVariables();// todo wat als er twee gelijke componenten in eenzelfde page zitten qua var => bv. items 1 en items2?

    function getInit($pages);

    function getConstructorVariables();

    function getHTML();
}