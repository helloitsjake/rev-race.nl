<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Laravel seedt bij RefreshDatabase eenmalig, in de eerste testklasse die de database
     * opzet. Stond $seed alleen op ExampleTest, dan hing de hele suite af van de
     * alfabetische volgorde: een nieuwe testklasse die eerder sorteert liet de database
     * ongeseed achter en ExampleTest viel om. Daarom staat het hier, voor alle tests.
     */
    protected bool $seed = true;
}
