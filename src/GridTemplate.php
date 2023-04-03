<?php

namespace Surrim\CssGridConfig;

class GridTemplate {
  private readonly string $gridTemplate;
  private ?array $usedGridAreas = null;

  public function __construct(string $gridTemplate) {
    $this->gridTemplate = $gridTemplate;
  }

  public function isUsedGridArea($gridArea): bool {
    return in_array($gridArea, $this->getUsedGridAreas());
  }

  public function getUsedGridAreas(): array {
    if ($this->usedGridAreas !== null) {
      return $this->usedGridAreas;
    }
    $templateGridAreas = explode('"', $this->gridTemplate);
    $usedGridAreas = [];
    foreach ($templateGridAreas as $i => $templateGridArea) {
      if ($i % 2 === 0) {
        continue;
      }
      $newGridAreas = explode(' ', $templateGridArea);
      foreach ($newGridAreas as $newGridArea) {
        if (self::isValidGridArea($newGridArea)) {
          $usedGridAreas[$newGridArea] = true;
        }
      }
    }
    ksort($usedGridAreas);
    return $this->usedGridAreas = array_keys($usedGridAreas);
  }

  private static function isValidGridArea(string $grid_area): bool {
    return preg_match('/^[_a-zA-Z\-][a-zA-Z0-9\-]*$/', $grid_area) && $grid_area !== 'auto';
  }

  public function equals(?GridTemplate $gridTemplate): bool {
    if ($gridTemplate === null) {
      return false;
    }
    return $this->gridTemplate === $gridTemplate->getGridTemplate();
  }

  public function getGridTemplate(): string {
    return $this->gridTemplate;
  }
}