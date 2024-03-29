<?php
interface IPage{
    function getImportStatement(string $path);
    function getRelativeImportStatement(array $pages,int $nestingLevel);
    function getDeclarationsStatement();
}