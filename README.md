# skeleton-test

## Description

This library contains the browser/UI testing utilities of the `skeleton`
framework. It performs these main tasks:

  - An engine-neutral Page Object model (`Scene` + `Page`) running on top of
    either Selenium or Playwright
  - Autoloading and discovery of test classes
  - PHPUnit integration (test cases, a result printer, console commands)
  - Optional storage of binary test fixtures (via `skeleton-file`)

## Installation

Installation via composer:

    composer require tigron/skeleton-test

Install `tigron/skeleton-console` as well to get the `test:*` console
commands, and `tigron/skeleton-core` to get the test autoloader.

## Configuration

|Configuration|Description|Default value|
|----|----|----|
|test_path|Directory where tests are located|null|
|driver|Driver used by a scene that does not declare one|'selenium'|
|browser|Browser used by the Selenium driver|'firefox'|
|selenium_hub|URL of the Selenium hub|'http://localhost:4444/wd/hub'|
|playwright_server|URL of the Playwright server|'ws://127.0.0.1:3000/playwright'|
|node_path|nodejs binary path used by Playwright|null|
|playwright_trace_path|Directory to write Playwright traces to. Tracing is disabled when null|null|
|default_implicit_timeout|Implicit timeout (in seconds) used by the Selenium driver when searching for elements|5|
|intense_count|Number of repeats performed by `test:intense`|10|
|start_timestamp_filename|File holding the timestamp the run started at, used for timings|null|
|timings_filename|File the per-scene start/stop timings are written to as json|null|

`start_timestamp_filename` and `timings_filename` are both optional; timing
is only recorded when both are set. The start timestamp file is written by
the first scene of the run if it does not exist yet, so it also marks the
beginning of the timeline a Selenium video recording would be aligned to.

## Features

### Writing tests

Tests are organized as **Scenes** and **Pages**:

  - a **Scene** is a PHPUnit test case: a sequence of test methods exercising
    one flow. Scenes assert.
  - a **Page** is a page object: it knows how to interact with one screen,
    and exposes that as plain methods. Pages never assert; they return data
    or booleans for the Scene to assert on.

Both are autoloaded from `Config::$test_path` through skeleton's regular
underscore-to-path convention (see
[skeleton-core](https://github.com/tigron/skeleton-core)): a class name maps
to a path by splitting on `_`:

    Scene_Shop_Checkout  =>  {test_path}/Scene/Shop/Checkout.php
    Page_Shop_Checkout   =>  {test_path}/Page/Shop/Checkout.php

Register the autoloader once, before discovering or running any test:

    \Skeleton\Test\Loader::register_autoloader($test_path);

#### Scenes

A Scene extends `\Skeleton\Test\Unit`:

    class Scene_Shop_Checkout extends \Skeleton\Test\Unit {

        protected static $driver = 'playwright';

        public function test_1_add_product(): void {
            $page = new Page_Shop_Checkout();
            $page->open();
            $page->add_product_to_basket('Widget');
        }
    }

`$driver` declares which Driver (`'selenium'` or `'playwright'`) every Page
instantiated in this scene runs on, and must be a single-quoted string
literal: the `test:list` console command detects it by scanning the file's
source rather than loading the class. A Scene without a `$driver`
declaration falls back to `Config::$driver`, and is considered **headless**:
plain PHP + database logic, without a browser.

Optionally override `setupBeforeScene()`/`tearDownAfterScene()` to set up or
clean up resources used by the whole scene.

#### Pages

A Page extends `\Skeleton\Test\Page` and implements `get_url()`:

    class Page_Shop_Checkout extends \Skeleton\Test\Page {

        public function get_url() {
            return 'https://example.be/checkout';
        }

        public function add_product_to_basket(string $name): void {
            $this->click('#product-' . $name);
            $this->wait_until_visible('.basket-item');
        }
    }

A Page is instantiated with no arguments when used inside a Scene: it then
runs on the driver declared by that scene. All pages instantiated within one
scene must use the same driver; mixing drivers within a single scene throws.
A Page can also be given an explicit driver name through its constructor,
independently of any active scene.

#### Helpers

Several Pages and Scenes often repeat the same multi-step interaction: a
login flow, driving a JS widget (an autosuggest, a date picker, ...),
closing a recurring modal. skeleton-test has no dedicated mechanism for
this, but a plain PHP trait works well: it is composed directly into the
class body, so it can freely call the Driver API methods (`$this->click()`,
`$this->wait_until_visible()`, ...) of whatever Page or Scene it is used in.

Name and locate the trait like any other autoloaded class
(`Helper_Datepicker` => `{test_path}/Helper/Datepicker.php`,
`Helper_Shop_Login` => `{test_path}/Helper/Shop/Login.php`), then `use` it in
every Page or Scene that needs it:

    trait Helper_Datepicker {

        public function datepicker_set_date(string $selector, \DateTime $date, ?string $within = null): void {
            $this->click($selector, $within);
            $this->wait_until_visible('.datepicker-dropdown');
            $this->click(".datepicker-dropdown [data-date='" . $date->format('Y-m-d') . "']");
            $this->wait_until_hidden('.datepicker-dropdown');
        }
    }

    class Page_Shop_Checkout extends \Skeleton\Test\Page {
        use Helper_Datepicker;

        public function get_url() {
            return 'https://example.be/checkout';
        }
    }

This keeps widget- or flow-specific logic in one place instead of
duplicated across every Page or Scene that needs it. A trait can `use`
other traits the same way, and can be added to a Scene (`\Skeleton\Test\Unit`)
instead of a Page when the logic belongs to the test flow itself rather than
to one screen (a login helper used at the start of many scenes, for example).

### The Driver API

Every Page exposes the engine-neutral Driver API (`\Skeleton\Test\Driver`):

|Method|Description|
|----|----|
|open_url($url)|Navigate to a URL|
|open()|Navigate to `get_url()` and check for an error page|
|refresh()|Refresh the current page|
|get_current_url()|Get the current page's URL|
|get_title()|Get the current page's title|
|click($selector, $within = null)|Click an element|
|fill($selector, $value, $within = null)|Clear an element and type a value into it|
|send_keys($selector, $keys, $within = null)|Type into an element without clearing it first|
|clear($selector, $within = null)|Clear an element|
|get_text($selector, $within = null)|Get the text of an element|
|get_attribute($selector, $attribute, $within = null)|Get an attribute of an element|
|is_displayed($selector, $within = null)|Whether the element is displayed (false, never throws, when absent/hidden)|
|is_selected($selector, $within = null)|Whether the element is selected (false, never throws, when absent)|
|is_enabled($selector, $within = null)|Whether the element is enabled (false, never throws, when absent)|
|count($selector, $within = null)|Count the elements matching a selector (0 when absent)|
|select_option_by_value($selector, $value, $within = null)|Select an option of a native `<select>` by value|
|select_option_by_index($selector, $index, $within = null)|Select an option of a native `<select>` by index|
|hover($selector, $within = null)|Hover over an element|
|drag_and_drop($selector, $target, $within = null)|Drag an element onto another element|
|execute_script($script, $arguments = [])|Run javascript in the browser (body style, `arguments[0]`, ...)|
|has_error(&$error)|Whether the current page shows an error|
|wait_until_visible/clickable/present/hidden($selector, $seconds = 10)|Wait for an element's state|
|wait_until($predicate, $seconds = 10)|Wait until a predicate returns true|
|set_implicit_timeout($seconds)|Set the Selenium implicit wait (no-op on Playwright)|
|get_driver()|Get the underlying Driver instance|

Selectors are css by default; a selector starting with `/` or `(` is treated
as xpath. Most methods accept an optional `$within` selector to scope the
lookup to a parent element. `wait_until_*` throw
`\Skeleton\Test\Exception\Elementnotfound` when the timeout is reached;
`count`/`is_*` never throw.

For driver-specific escape hatches, call `native()` on the Driver instance
(`$this->get_driver()->native()`) to get the underlying engine object
(`Facebook\WebDriver\...` on selenium, a Playwright `Page` on playwright).

### Drivers

#### Selenium

Configured through `Config::$selenium_hub` and `Config::$browser`. On
construction, the driver retries connecting for a while to absorb the delay
between a freshly started Selenium hub reporting ready and its nodes
actually registering.

#### Playwright

Configured through `Config::$playwright_server` and, if needed,
`Config::$node_path`. All pages of a scene share one browser context and,
within that context, one page per scene. Setting
`Config::$playwright_trace_path` enables tracing: a `.zip` trace file per
scene is written to that directory when the scene tears down.

### Exceptions

  - `\Skeleton\Test\Exception\Elementnotfound`: thrown by the `wait_until_*`
    Driver methods when their condition is not met within the timeout.
  - `\Skeleton\Test\Exception\Timingfilenotfound`: thrown when
    `Config::$timings_filename` is configured but
    `Config::$start_timestamp_filename` is missing by the time a scene tears
    down.

### Console commands

These require `tigron/skeleton-console`.

|Command|Description|
|----|----|
|test:all|Run every scene found under `Config::$test_path`|
|test:run NAME[,NAME...]|Run one or more scenes by class name|
|test:intense NAME[,NAME...]|Run one or more scenes `Config::$intense_count` times|
|test:list \<playwright\|selenium\|headless\>|List, comma-separated, the scenes under `{test_path}/Scene` declaring the given driver — useful to split a run across engines in CI|
|test:file add/get/delete/list|Manage binary test data files, requires `tigron/skeleton-file`|

`test:all`, `test:run` and `test:intense` accept `--disable-pretty-printer`
to fall back to PHPUnit's own result printer instead of
`\Skeleton\Test\Printer`, which prints every test on its own line with a
status color and duration.

### Test data files

When `tigron/skeleton-file`, `tigron/skeleton-object` and
`tigron/skeleton-database` are installed, binary fixtures (files a test
needs to upload, for example) can be stored and retrieved by an identifier:

    skeleton test:file add identifier /var/www/mysite/my_file.txt
    skeleton test:file get identifier /var/www/mysite/target_file.txt
    skeleton test:file delete identifier
    skeleton test:file list

Programmatically, through `\Skeleton\Test\Test\Data\File`:

    $file = \Skeleton\Test\Test\Data\File::get_by_identifier('identifier');
    $contents = $file->file->get_contents();
