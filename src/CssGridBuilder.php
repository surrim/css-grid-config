<?php

namespace Surrim\CssGridConfig;

class CssGridBuilder {
  private CssGrid $cssGrid;

  public function __construct(string $gridCssSelector) {
    $this->cssGrid = new CssGrid($gridCssSelector);
  }

  public function addRegion(string $cssSelector, string $gridArea): CssGridBuilder {
    $this->cssGrid->addRegion($cssSelector, $gridArea);
    return $this;
  }

  public function addCompositeRegion(string $cssSelector, string $defaultGridArea): CssGridBuilder {
    $this->cssGrid->addCompositeRegion($cssSelector, $defaultGridArea);
    return $this;
  }

  public function addBreakpoint(?int $minWidth, array $customProperties = [], ?string $gridTemplate = null, array $usedCompositeRegions = []): CssGridBuilder {
    $this->cssGrid->addBreakpoint($minWidth, $customProperties, $gridTemplate, $usedCompositeRegions);
    return $this;
  }

  public function build(): CssGrid {
    return $this->cssGrid;
  }
}