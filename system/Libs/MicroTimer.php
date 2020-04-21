<?php


namespace System\Libs;


 class MicroTimer
{
    private int $start_time;

	public function start():int
    {
		$mtime = microtime();
		$mtime = explode( ' ', $mtime );
		$mtime = $mtime[1] + $mtime[0];
		$this->start_time = $mtime;
		return true;
	}

	public function stop():int
    {
		$mtime = microtime();
		$mtime = explode( ' ', $mtime );
		$mtime = $mtime[1] + $mtime[0];
		$end_time = $mtime;
        return round( ($end_time - $this->start_time), 5 );
	}
}
