<?php
class Uploader extends Field
{
	protected $uploadDescription;
	protected $featureName;
	protected $duplicteCounter;
	protected $value;

	public function setUploader($uploadDescription = false, $featureName = false, $duplicateCounter = false, $value = false)
	{
		$this->uploadDescription = $uploadDescription;
		$this->featureName = $featureName;
		$this->duplicateCounter = $duplicateCounter;
		$this->value = $value;
	}
}