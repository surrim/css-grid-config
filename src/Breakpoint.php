<?php

namespace Surrim\CssGridConfig;

class Breakpoint {
  private readonly int $minWidth;
  private array $customProperties = [];
  private ?GridTemplate $gridTemplate = null;
  private array $usedCompositeRegions = [];

  public function __construct(?int $minWidth = null) {
    $this->minWidth = $minWidth ?? 0;
  }

  public function getCustomProperties(): array {
    return $this->customProperties;
  }

  public function setCustomProperties(array $customProperties): Breakpoint {
    foreach ($customProperties as $key => $value) {
      $this->addCustomProperty($key, $value);
    }
    return $this;
  }

  public function addCustomProperty(string $key, string $value): Breakpoint {
    $this->customProperties[$key] = $value;
    return $this;
  }

  public function getGridTemplate(): ?GridTemplate {
    return $this->gridTemplate;
  }

  public function setGridTemplate(?GridTemplate $gridTemplate): Breakpoint {
    if ($gridTemplate !== null) {
      $this->gridTemplate = $gridTemplate;
    }
    return $this;
  }

  public function isUsedCompositeRegion(string $regionCssSelector): bool {
    return array_key_exists($regionCssSelector, $this->usedCompositeRegions);
  }

  public function setUsedCompositeRegions(array $usedCompositeRegions): Breakpoint {
    foreach ($usedCompositeRegions as $cssSelector => $gridArea) {
      $this->addUsedCompositeRegion($cssSelector, $gridArea);
    }
    return $this;
  }

  public function addUsedCompositeRegion(string $cssSelector, ?string $gridArea): Breakpoint {
    $this->usedCompositeRegions[$cssSelector] = $gridArea;
    return $this;
  }

  public function getMinWidth(): int {
    return $this->minWidth;
  }

  public function getUsedCompositeRegionGridArea(string $regionCssSelector): ?string {
    return $this->usedCompositeRegions[$regionCssSelector] ?? null;
  }
}