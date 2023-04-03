<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Surrim\CssGridConfig\CssGridBuilder;

class CssGridConfigTest extends TestCase {
  public function testSerialize(): void {
    $cssGrid = (new CssGridBuilder('body'))
        ->addRegion('#header', 'hh')
        ->addRegion('#content', 'cc')
        ->addBreakpoint(0, [], '"hh" "cc"')
        ->addBreakpoint(600, [], '"cc" "hh"')
        ->build();
    $css = $cssGrid->generate();

    $serializedCss = $css->serialize();
    $this->assertStringContainsString('body{display:grid !important;grid-template:"hh" "cc"}', $serializedCss);
    $this->assertStringContainsString('#content{grid-area:cc}', $serializedCss);
    $this->assertStringContainsString('#header{grid-area:hh}', $serializedCss);
    $this->assertStringContainsString('@media(min-width:600px){body{grid-template:"cc" "hh"}}', $serializedCss);

    $prettySerializedCss = $css->prettySerialize();
    $this->assertStringContainsString('body { display: grid !important; grid-template: "hh" "cc"; }', $prettySerializedCss);
    $this->assertStringContainsString('#content { grid-area: cc; }', $prettySerializedCss);
    $this->assertStringContainsString('#header { grid-area: hh; }', $prettySerializedCss);
    $this->assertStringContainsString('#header { grid-area: hh; }', $prettySerializedCss);
    $this->assertStringContainsString(<<<'EOF'
      @media (min-width: 600px) {
        body { grid-template: "cc" "hh"; }
      }
      EOF, $prettySerializedCss);
  }
}
