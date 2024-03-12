<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arResult */
define('IBLOCK_ID', $arResult['ID']);
global $elements, $result;
$result = [];

$elements = rebuildElements($arResult['ITEMS']);
$sectionTree = getSectionsTree();
$arResult['ITEMS'] = sortElements($sectionTree);

function sortElements(array $sections): array {
    global $result, $elements;

    if (count($sections) >= 2) {
        uasort($sections, 'sortByIndex');
    }

    foreach ($sections as $section) {
        if (isset($elements[$section['ID']])) {
            uasort($elements[$section['ID']], 'sortByIndex');
            $result = array_merge($result, $elements[$section['ID']]);
        }

        if (isset($section['CHILD'])) {
            sortElements($section['CHILD']);
        }
    }

    return $result;
}

function sortByIndex(array $first, array $second): int {
    $pattern = '~\(\K.+?(?=\))~';
    preg_match($pattern, $first['NAME'], $f);
    preg_match($pattern, $second['NAME'], $s);

    return ($f[0] > $s[0]) ? 1 : -1;
}

function getSectionsTree(): array {
    $sectionLinc = [];

    $rsSections = CIBlockSection::GetList(['DEPTH_LEVEL'=>'ASC'], ['IBLOCK_ID' => IBLOCK_ID], false, ['*']);
    while($arSection = $rsSections->GetNext()) {
        $sectionLinc[intval($arSection['IBLOCK_SECTION_ID'])]['CHILD'][$arSection['ID']] = $arSection;
        $sectionLinc[$arSection['ID']] = &$sectionLinc[intval($arSection['IBLOCK_SECTION_ID'])]['CHILD'][$arSection['ID']];
    }

    return $sectionLinc[0]['CHILD'];
}

function rebuildElements($elements): array {
    $reassembledElements = [];

    foreach ($elements as $element) {
        $reassembledElements[$element['IBLOCK_SECTION_ID']][] = $element;
    }

    return $reassembledElements;
}