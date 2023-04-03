<?php

namespace Surrim\CssGridConfig;

use Closure;
use Surrim\CssLib\Css;
use Surrim\CssLib\MediaQueryBlock\WidthMediaQuerySelector;

class CssGrid {
  private readonly string $gridCssSelector;
  private array $regions = [];
  private array $compositeRegions = [];
  private array $breakpoints = [];

  public function __construct(string $gridCssSelector) {
    $this->gridCssSelector = $gridCssSelector;
  }

  public function addRegion(string $cssSelector, string $gridArea): void {
    $this->regions[$cssSelector] = $gridArea;
  }

  public function addCompositeRegion(string $cssSelector, string $defaultGridArea): void {
    $this->compositeRegions[$cssSelector] = $defaultGridArea;
  }

  public function addBreakpoint(?int $minWidth, array $customProperties = [], ?string $gridTemplate = null, array $usedCompositeRegions = []): void {
    $this->breakpoints[$minWidth ?? 0] = (new Breakpoint($minWidth))
        ->setCustomProperties($customProperties)
        ->setGridTemplate(new GridTemplate($gridTemplate))
        ->setUsedCompositeRegions($usedCompositeRegions);
  }

  public function generate(): Css {
    ksort($this->regions);
    ksort($this->compositeRegions);
    ksort($this->breakpoints);

    $css = new Css();
    $this->addStaticCssRules($css);
    $this->addBreakpointsCssRules($css);
    $this->addRegionsCssRules($css);
    $this->addCompositeRegionsCssRules($css);
    return $css;
  }

  private function addStaticCssRules(Css $css): void {
    $css->addRules(null, [$this->gridCssSelector], ['display' => 'grid !important']);
  }

  private function addBreakpointsCssRules(Css $css): void {
    $lastGridTemplate = null;
    $allCustomProperties = [];
    /** @var Breakpoint $breakpoint */
    foreach ($this->breakpoints as $breakpoint) {
      $mediaQuerySelector = new WidthMediaQuerySelector($breakpoint->getMinWidth());
      foreach ($breakpoint->getCustomProperties() as $customPropertyId => $customProperty) {
        if ($customProperty !== ($allCustomProperties[$customPropertyId] ?? null)) {
          $css->addRules(
              $mediaQuerySelector,
              [$this->gridCssSelector],
              ['--' . $customPropertyId => $customProperty]
          );
          $allCustomProperties[$customPropertyId] = $customProperty;
        }
      }

      $gridTemplate = $breakpoint->getGridTemplate() ?? $lastGridTemplate;
      if (!$gridTemplate->equals($lastGridTemplate)) {
        $css->addRules(
            $mediaQuerySelector,
            [$this->gridCssSelector],
            ['grid-template' => $gridTemplate->getGridTemplate()]
        );
        $lastGridTemplate = $gridTemplate;
      }
    }
  }

  private function addRegionsCssRules(Css $css): void {
    $regions = array_keys($this->regions);
    $breakpointContainsRegionClosure = fn(Breakpoint $breakpoint, string $regionCssSelector) => $breakpoint->getGridTemplate()->isUsedGridArea($this->regions[$regionCssSelector]);
    $addUsedRegionCssClosure = function (Css $css, string $regionCssSelector) {
      $regionGridArea = $this->regions[$regionCssSelector];
      $css->addRules(null, [$regionCssSelector], ['grid-area' => $regionGridArea]);
    };

    $this->addAnyRegionsCssRules($css, $regions, $breakpointContainsRegionClosure, $addUsedRegionCssClosure);
  }

  private function addAnyRegionsCssRules(Css $css, array $regions, Closure $breakpointContainsRegionClosure, Closure $addUsedRegionCssClosure): void {
    foreach ($regions as $regionCssSelector) {
      $minWidth = 0;
      $usedState = true;
      /** @var Breakpoint $breakpoint */
      foreach ($this->breakpoints as $breakpoint) {
        $gridAreaIsUsed = $breakpointContainsRegionClosure($breakpoint, $regionCssSelector);
        if ($usedState) {
          if (!$gridAreaIsUsed) {
            $minWidth = $breakpoint->getMinWidth();
            $usedState = false;
          }
        } else { // ! $usedState
          if ($gridAreaIsUsed) {
            $maxWidth = $breakpoint->getMinWidth();
            $this->addCssDisplayNone($css, $minWidth, $maxWidth, $regionCssSelector);
            $usedState = true;
          }
        }
      }
      if (!$usedState && $minWidth === 0) {
        $this->addCssDisplayNone($css, $minWidth, null, $regionCssSelector);
      } else {
        $addUsedRegionCssClosure($css, $regionCssSelector);
      }
    }
  }

  public function addCssDisplayNone(Css $css, ?int $minWidth, ?int $maxWidth, string $regionCssSelector): void {
    $css->addRules(new WidthMediaQuerySelector($minWidth, $maxWidth), [$regionCssSelector], ['display' => 'none !important'], false);
  }

  private function addCompositeRegionsCssRules(Css $css): void {
    $regions = array_keys($this->compositeRegions);
    $breakpointContainsRegionClosure = fn(Breakpoint $breakpoint, string $regionCssSelector) => $breakpoint->isUsedCompositeRegion($regionCssSelector);
    $addUsedRegionCssClosure = function (Css $css, string $regionCssSelector) {
      $lastRegionGridArea = null;
      /** @var Breakpoint $breakpoint */
      foreach ($this->breakpoints as $breakpoint) {
        if (!$breakpoint->isUsedCompositeRegion($regionCssSelector)) {
          continue;
        }

        $usedRegionGridArea =
            $breakpoint->getUsedCompositeRegionGridArea($regionCssSelector)
            ?? $lastRegionGridArea
            ?? $this->compositeRegions[$regionCssSelector];
        if ($usedRegionGridArea === $lastRegionGridArea) {
          continue;
        }

        $breakpointMinWidth = $breakpoint->getMinWidth();
        $css->addRules(new WidthMediaQuerySelector($breakpointMinWidth), [$regionCssSelector], ['grid-area' => $usedRegionGridArea]);
        $lastRegionGridArea = $usedRegionGridArea;
      }
    };

    $this->addAnyRegionsCssRules($css, $regions, $breakpointContainsRegionClosure, $addUsedRegionCssClosure);
  }
}