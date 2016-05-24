<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    public function watchLinks()
    {
        $filename = 'links.txt';

        $this->taskWatch()
        ->monitor($filename, function() use($filename) {
            $exec = $this->taskExec(sprintf('git diff %s', $filename))->printed(false)->run();

            /* match all lines that start with -|+ and does not have another +|- */
            $pattern = '/^(\+{1}|\-{1})(?!(\+|\-)).*/';
            /* (PCRE_MULTILINE) */
            $modifier = 'm';

            $output = '';
            preg_match_all(sprintf('%s%s',$pattern, $modifier), $exec->getMessage(), $output);

            if(!isset($output[0])) return;

            for ($i=0; $i < count($output[0]); $i++) {
                $line = $output[0][$i];
                $symbol = $line[0];
                // removes + or - symbol
                $line = substr($line, 1, strlen($line));

                $symbol == '+' ? $preMessage = 'Adds' : $preMessage = 'Removes';
                $commitMessage[] = sprintf("%s %s", $preMessage, $line);
            }

            $this->taskExec(sprintf('git commit -m "%s" %s', implode(', ', $commitMessage), $filename))->printed(false)->run();

            $this->say(implode(', ', $commitMessage));
        })->run();
    }
}
