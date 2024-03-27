<?php
interface IPage{
    function getImportStatement(string $path);
    function getDeclarationsStatement();
}