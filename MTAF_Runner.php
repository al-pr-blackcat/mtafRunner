<?php
class MTAF_Runner
{
    private $MTAFPath = "/var/www/html/eotica-mtaf/";
    private $logsPath = "/var/www/html/eotica-mtaf/tmp/";
    private $runCommand = "/var/www/html/eotica-mtaf/runtests.sh";
    private $resultsURL = 'http://centos.eotica.mtaf/tmp/';
    private $lockFile = 'mtaf.lock';
    private $logFiles = array('logfile.json', 'logfile.tap', 'logfile.txt', 'logfile.xml', 'testdox.html', 'testdox.txt');
    private $logXML = 'logfile.xml';

    function __construct() {
        if (!is_dir($this->MTAFPath) || !is_dir($this->logsPath)) {
            echo "Wrong configuration! Please check settings before run!";
            exit;
        }
    }

    /**
     * Checks, if we have lockfile for run, if so, trying to read it and check state of process
     * @return string state
     * @state ready
     */
    public function checkState()
    {
        // check if we have lock and get pid of running process if present
        $pid = $this->checkLock();
        if ($pid) {
            // check process state
            $processState = $this->checkProcess($pid);
            if ($processState) {
                return 'running';
            }
        }
        // check if we have full results
        $haveResults = $this->checkResultPresent();
        if ($haveResults) {
            // we have full set of results,
            // but lock wasn't removed for some reason - remove
            $this->clearLock();
            return "finished";
        } else {
            // we have no or partial results
            // assume process terminated unexpectedly, remove lock
            $this->clearLock();
            return "unfinished";
        }
    }

    /**
     * initiate run
     * @return bool
     */
    public function run()
    {
        $state = $this->checkState();
        if ($state != "running") {

            // run
            try {
                // var_dump($this->runCommand);
                // $PID = shell_exec("nohup ".$this->MTAFPath.$this->runCommand." > /dev/null & echo $!");
                $PID = shell_exec("nohup  sh ".$this->runCommand." > ".$this->logsPath."res & echo \$!");
                // var_dump($PID);
                $fp = fopen($this->logsPath . $this->lockFile, "w");
                fwrite($fp, $PID);
                fclose($fp);
                return true;
            } catch(exception $e) {
                echo "Error starting script: ".$e;
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * kills process
     * @return bool
     */
    public function stop()
    {
        // check state of the process by lock
        $state = $this->checkState();
        if ($state == "running") {
            // if process running - stop it
            // get pid
            $pid = $this->checkLock();
            if ($pid)
                // kill process by pid
                exec("kill $pid");
		  exec("killall phpunit");
            // kill process by pid
            return true;
        }
        //clear lock
        $this->clearLock();
    }

    /**
     * check if $locFile present, and if so, read it
     * @return NULL|PID of process from lockfile
     */
    private function checkLock()
    {
        if (is_file($this->logsPath . $this->lockFile)) {
            // if we have lock file present
            // read pid from it
            $lockFile = file($this->logsPath . $this->lockFile);
            $pid = $lockFile[0];
            return $pid;
        } else {
            return null;
        }
    }

    /**
     * @param $pid int of the process to check for state
     * @return state
     */
    private function checkProcess($pid)
    {
        exec("ps $pid", $processState);
        return (count($processState) >= 2);
    }

    /**
     * looks in results folder if we have completed results
     * @return bool true, if we have finished results of run
     */
    private function checkResultPresent()
    {
        if(is_file($this->logsPath.$this->logXML)) {
            $logXML = file($this->logsPath.$this->logXML);
            if (!empty($logXML)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * looks in results folder if we have some results
     * @return bool true, if we have some results of run
     */
    public function checkPartialResultPresent()
    {
        foreach($this->logFiles as $logFile) {
            $path = $this->logsPath.$logFile;
            if (is_file($path)) {
                return true;
            }
        }
        return false;
    }

    public function listAvailableResults() {
        $availableResults = array();
        foreach($this->logFiles as $logFile) {
            $path = $this->logsPath.$logFile;
            if (is_file($path)) {
                $availableResults[] = $logFile;
            }
        }
        return $availableResults;
    }

    /**
     * Return URL for results folder
     * @return string URL
     */
    public function getResulstURL() {
        return $this->resultsURL;
    }

    /**
     * removes lock file
     */
    private function clearLock()
    {
        if (is_file($this->logsPath.$this->lockFile))
            unlink($this->logsPath.$this->lockFile);
    }

}