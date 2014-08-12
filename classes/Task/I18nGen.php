<?php

class Task_I18nGen extends Minion_Task {

	protected $_options = array('lang' => NULL);

	protected function _execute(array $params) {
		if(empty($params['lang'])) {
			Minion_CLI::write(Minion_CLI::color('Please, specify --lang param', 'red'));
			return;
		}

		$aViews = Kohana::list_files('views');

		$aStrings = array();

		Arr::map(function($sFileName) use(&$aStrings) {
			if(strtolower(pathinfo($sFileName, PATHINFO_EXTENSION)) != 'php') {
				Minion_CLI::write('Skipping ' . $sFileName);
				return;
			}

			$sFile = file_get_contents($sFileName);
				preg_match_all('!__\([\'"](.*?)[\'"]\s{0,10}[),]!si', $sFile, $aMatches);
				$aStrings = array_merge($aStrings, $aMatches[1]);
			}, $aViews
		);


		self::_write($aStrings, $params['lang']);
	}

	protected function _write($aStrings, $sLang) {

		$aContents[] = "<?php defined('SYSPATH') or die('No direct script access.');";
		$aContents[] = "";
		$aContents[] = "/**";
		$aContents[] = "* Translation file in language: " . $sLang;
		$aContents[] = "* Automatically generated from all views we found.";
		$aContents[] = "*/";
		$aContents[] = "";
		$aContents[] = "return ".var_export(array_combine($aStrings, $aStrings), true).';';
		$aContents = implode(PHP_EOL, $aContents);

		// save string to file
		$sSavePath = APPPATH.'/i18n/';
		$sFilename = $sLang. '.php';

		// check that the path exists
		if (!file_exists($sSavePath))
		{
			// if not, create directory
			mkdir($sSavePath, 0777, true);
		}

		// save the file
		file_put_contents($sSavePath . $sFilename, $aContents);
	}
}