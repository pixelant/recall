# Selective Recall (ext:recall)
TYPO3 extension that can remember settings from a different request using a hash. E.g. recalling settings or data used in the main request within an eID request.

## Installation

1. Install the extension using Composer: `composer req pixelant/recall`
2. Activate the extension in TYPO3 by using the _Admin Tools > Extensions_ module or by running `vendor/bin/typo3 extension:activate recall; vendor/bin/typo3cms database:updateschema` in the command line.

## Usage

Initializing recallable data in a normal controller class:

```PHP
// use Pixelant\Recall\Service\RecallService
// use TYPO3\CMS\Core\Page\PageRenderer
$recallService = GeneralUtility::makeInstance(RecallService::class);
$recallHash = $recallService->set(['settings' => $this->settings]);

$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
$pageRenderer->addInlineSettingArray(
    'tx_myextension',
    ['recallHash' => $recallHash]
);
```

Make an Ajax call to fetch data using eID:

```JavaScript
$.ajax(
	'/?eID=tx_myextension_getmore&recall=' + TYPO3.settings.tx_myextension.recallHash
);
```

Handle the eID in a Controller. (Note that eID controllers do not initialize a settings array.)

```PHP
$recallHash = $request->getQueryParams()['recall'];

$settings = $this->recallService->get($recallHash)['settings'];
```

You now have access to the settings from the original request without having to initialize configuration or think about page IDs.

Note: You can of course also supply less information than the entire settings array.

## Bugs, contribution, and feature requests

Bug reports, pull requests, and feature requests are very welcome. [Create a bug report or feature request](https://github.com/pixelant/recall/issues/new)
