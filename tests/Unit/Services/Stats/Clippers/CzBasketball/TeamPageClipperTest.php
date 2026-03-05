<?php

namespace Tests\Unit\Services\Stats\Clippers\CzBasketball;

use App\Services\Stats\Clippers\CzBasketball\CzBasketballTeamPageClipper;
use PHPUnit\Framework\TestCase;

class TeamPageClipperTest extends TestCase
{
    private function sampleHtml(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<body>
  <h1>Sokol Kbely C</h1>
  <div class="info">
    <p>Klub: Sokol Kbely</p>
    <p>Kategorie: Muži</p>
    <p>Soutěž: Přebor B</p>
  </div>

  <div id="tab-pane-one">
    <h2>Soupiska</h2>
    <table>
      <thead>
        <tr>
          <th>#</th><th>Hráč</th><th>Pozice</th><th>Rok narození</th><th>Min.</th><th>TH %</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td><td><a href="/hrac/111">Jan Novak</a></td><td>G</td><td>1990</td><td>30</td><td>80</td>
        </tr>
        <tr>
          <td>2</td><td><a href="/hrac/222">Petr Svoboda</a></td><td>F</td><td>1992</td><td>28</td><td>75</td>
        </tr>
        <tr>
          <td>3</td><td><a href="/hrac/333">Karel Malý</a></td><td>C</td><td>1991</td><td>26</td><td>70</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div id="tab-pane-three">
    <h2>Zápasy</h2>
    <table>
      <thead>
        <tr>
          <th>Číslo utkání</th><th>Soutěž</th><th>Domácí/hosté</th><th>Datum</th><th>Soupeř</th><th>Skóre</th><th>2B</th><th>3B</th><th>TH</th><th>TH %</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>104</td><td><a href="/soutez/4625">Přebor B</a></td><td>Domácí</td><td>2026-03-01</td><td><a href="/tym/7764">TJ ČSA</a></td><td>79:46</td><td>10</td><td>5</td><td>9</td><td>70</td>
        </tr>
        <tr>
          <td>105</td><td><a href="/soutez/4625">Přebor B</a></td><td>Hosté</td><td>2026-03-08</td><td><a href="/tym/8888">BK Test</a></td><td>65:60</td><td>8</td><td>7</td><td>10</td><td>68</td>
        </tr>
        <tr>
          <td>106</td><td><a href="/soutez/4625">Přebor B</a></td><td>Domácí</td><td>2026-03-15</td><td><a href="/tym/9999">USK</a></td><td>--</td><td></td><td></td><td></td><td></td>
        </tr>
      </tbody>
    </table>
  </div>

  <div id="tab-pane-four">
    <h2>Historie</h2>
    <table>
      <thead>
        <tr>
          <th>Sezóna</th><th>Soutěž</th><th>Umístění</th><th>Počet bodů</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>2024/25</td><td><a href="/soutez/4625">Přebor B</a></td><td>2.</td><td>34</td>
        </tr>
      </tbody>
    </table>
  </div>

</body>
</html>
HTML;
    }

    public function test_team_page_clipper_finds_required_clips()
    {
        $html = $this->sampleHtml();
        $clipper = new CzBasketballTeamPageClipper();
        $clips = $clipper->clip($html, 'https://cz.basketball/tym/123?y=2025');

        $this->assertNotEmpty($clips, 'No clips returned');

        $ids = array_map(fn($c) => $c->id, $clips);
        $this->assertContains('roster_table', $ids, 'Roster table not found');
        $this->assertContains('matches_table', $ids, 'Primary matches table not found');
        $this->assertContains('history_table', $ids, 'History table not found');

        // Extracted links JSON
        $json = $clipper->buildExtractedLinksJson($clips);
        $data = json_decode($json, true);
        $this->assertGreaterThanOrEqual(3, count($data['players']));
        $this->assertGreaterThanOrEqual(2, count($data['matches']));

        // CNH size test
        $cnh = $clipper->buildCnh($clips);
        $this->assertLessThan(80000, strlen($cnh), 'CNH exceeds 80KB limit');

        // Determinism: hash by build twice
        $cnh2 = $clipper->buildCnh($clips);
        $this->assertSame(sha1($cnh), sha1($cnh2), 'CNH build is not deterministic');
    }
}
