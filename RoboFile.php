<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    /**
     * Runs a php server
     */
    public function server($opt = ['port' => 8000])
    {
        $this->taskServer($opt['port'])
            ->dir('public/ phpserver.php')
            ->run();
    }

    /**
     * Installs assets to public directory
     */
    public function assets()
    {
        $dir = 'public/assets';

        if (!is_dir($dir)) {
            $this->taskFileSystemStack()
                ->mkdir($dir)
                ->chmod($dir, 755, null, true)
                ->run();
        } else {
            $this->taskCleanDir($dir)->run();
        }

        $this->taskFileSystemStack()
            ->mkdir($dir.'/css')
            ->mkdir($dir.'/js')
            ->mkdir($dir.'/fonts')
            ->copy('bower_components/jquery/dist/jquery.min.js', $dir.'/js/jquery.min.js')
            ->copy('bower_components/bootstrap/dist/js/bootstrap.min.js', $dir.'/js/bootstrap.min.js')
            ->copy('bower_components/bootstrap/dist/css/bootstrap.min.css', $dir.'/css/bootstrap.min.css')
            ->copy('bower_components/angular/angular.min.js', $dir.'/js/angular.min.js')
            ->copy('bower_components/angular-resource/angular-resource.min.js', $dir.'/js/angular-resource.min.js')
            ->copy('bower_components/jsoneditor/jsoneditor.min.js', $dir.'/js/jsoneditor.min.js')
            ->copy('bower_components/jsoneditor/jsoneditor.min.css', $dir.'/css/jsoneditor.min.css')
            ->copy('bower_components/jsoneditor/asset/jsonlint/jsonlint.js', $dir.'/js/jsonlint.js')
            ->copy('app/app.js', $dir.'/js/app.js')
            ->run();

            $this->taskMirrorDir(['bower_components/bootstrap/dist/fonts' => $dir.'/fonts'])->run();
            $this->taskMirrorDir(['bower_components/jsoneditor/img' => $dir.'/css/img'])->run();
            $this->taskMirrorDir(['bower_components/jsoneditor/asset/ace' => $dir.'/js/ace'])->run();
    }

    public function watch()
    {
        $this->taskWatch()
            ->monitor('app/app.js', function() {
                $this->taskFileSystemStack()
                    ->copy('app/app.js', 'public/assets/js/app.js')
                    ->run();
            })
            ->run();
    }
}
