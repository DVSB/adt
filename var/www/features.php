<!DOCTYPE html>
<?php
require_once(dirname(__FILE__) . '/lib/PHPGit/Repository.php');
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta http-equiv="refresh" content="120">
    <title>Acceptance Live Instances</title>
    <link rel="shortcut icon" type="image/x-icon" href="/images/favicon.ico"/>
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.2/css/bootstrap-combined.min.css" type="text/css" rel="stylesheet" media="all">
    <link href="//netdna.bootstrapcdn.com/bootswatch/2.1.1/spacelab/bootstrap.min.css" type="text/css" rel="stylesheet" media="all">
    <link href="./style.css" media="screen" rel="stylesheet" type="text/css"/>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js" type="text/javascript"></script>
    <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.2/js/bootstrap.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-1292368-28']);
        _gaq.push(['_trackPageview']);

        (function () {
            var ga = document.createElement('script');
            ga.type = 'text/javascript';
            ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ga, s);
        })();

    </script>
</head>
<body>
<!-- navbar ================================================== -->
<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container-fluid">
            <a class="brand" href="#"><?=$_SERVER['SERVER_NAME'] ?></a>
            <ul class="nav">
                <li><a href="/">Home</a></li>
                <li class="active"><a href="/features.php">Features</a></li>
                <li><a href="/servers.php">Servers</a></li>
            </ul>
        </div>
    </div>
</div>
<!-- /navbar -->
<!-- Main ================================================== -->
<div id="wrap">
    <div id="main">
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span12">
                    <?php
                    function getGitDirectoriesList($directory)
                    {
                        // create an array to hold directory list
                        $results = array();
                        // create a handler for the directory
                        $handler = opendir($directory);
                        // open directory and walk through the filenames
                        while ($file = readdir($handler)) {
                            // if file isn't this directory or its parent, add it to the results
                            if ($file != "." && $file != ".." && strpos($file, ".git")) {
                                $results[] = $file;
                            }
                        }
                        // tidy up: close the handler
                        closedir($handler);
                        // done!
                        return $results;
                    }
                    //List all repos
                    $repos = getGitDirectoriesList($_SERVER['ADT_DATA'] . "/sources/");
                    asort($repos);
                    $features = array();
                    foreach ($repos as $repoDirName) {
                        $repoObject = new PHPGit_Repository($_SERVER['ADT_DATA'] . "/sources/" . $repoDirName);
                        $branches = array_filter(preg_replace('/.*\/feature\//', '', explode("\n", $repoObject->git('branch -r --list */feature/*'))));
                        foreach ($branches as $branch) {
                            $fetch_url = $repoObject->git('config --get remote.origin.url');
                            if (preg_match("/git:\/\/github\.com\/(.*)\/(.*)\.git/", $fetch_url, $matches)) {
                                $github_org = $matches[1];
                                $github_repo = $matches[2];
                            }
                            $features[$branch][$repoDirName]['http_url'] = "https://github.com/" . $github_org . "/" . $github_repo . "/tree/feature/" . $branch;
                            $behind_commits_logs = $repoObject->git("log origin/feature/" . $branch . "..origin/master --oneline");
                            if (empty($behind_commits_logs))
                                $features[$branch][$repoDirName]['behind_commits'] = 0;
                            else
                                $features[$branch][$repoDirName]['behind_commits'] = count(explode("\n", $behind_commits_logs));
                            $ahead_commits_logs = $repoObject->git("log origin/master..origin/feature/" . $branch . " --oneline");
                            if (empty($ahead_commits_logs))
                                $features[$branch][$repoDirName]['ahead_commits'] = 0;
                            else
                                $features[$branch][$repoDirName]['ahead_commits'] = count(explode("\n", $ahead_commits_logs));
                        }
                    }
                    ksort($features);
                    ?>
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>Feature</th>
                            <?php foreach ($repos as $repoDirName) { ?>
                                <th class="col-center"><?=substr($repoDirName, 0, -4)?></th>
                            <?php } ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($features as $feature => $projects) { ?>
                            <tr>
                                <td><?=$feature?></td>
                                <?php foreach ($repos as $repoDirName) { ?>
                                    <td>
                                        <?php if (array_key_exists($repoDirName, $projects)) { ?>
                                            <a href="<?=$projects[$repoDirName]['http_url']?>" target="_blank" title="Repository URL"><i class="icon-ok"></i> <?=$projects[$repoDirName]['behind_commits']?> behind / <?=$projects[$repoDirName]['ahead_commits']?> ahead</a>
                                        <?php }?>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /container -->
    </div>
</div>
<!-- Footer ================================================== -->
<div id="footer">Copyright © 2000-2013. All rights Reserved, eXo Platform SAS.</div>
</body>
</html>
