<?php
namespace phpbob\representation\traits;

trait NameChangeSubjectTrait {
	protected $nameChangeClosures = [];
	protected $name;
	
	public function onNameChange(\Closure $closure) {
		$this->nameChangeClosures[] = $closure;
	}
	
	private function triggerNameChange(string $oldName, string $newName) {
		foreach ($this->nameChangeClosures as $nameChangeClosure) {
			$nameChangeClosure($oldName, $newName);
		}
	}
	
	public function getName(): string {
		return $this->name;
	}
	
	public function setName(string $name) {
		if ($this->name !== $name) {
			$this->triggerNameChange($this->name, $name);
		}
			
		$this->name = $name;
		
		return $this;
	}
}