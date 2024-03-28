<?php
interface IPage{
    function getImportStatement(string $path);
    function getRelativeImportStatement(int $nestingLevel);
    function getDeclarationsStatement();
}