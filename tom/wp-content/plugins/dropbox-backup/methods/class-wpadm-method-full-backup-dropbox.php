<?php
/**
* Creates a full backup of the site
* Class WPadm_Method_Backup_Dropbox
*/
if (!class_exists('WPadm_Method_Full_Backup_Dropbox')) {
    class WPadm_Method_Full_Backup_Dropbox extends WPAdm_Method_Class {
        /**
        * uniqueid 
        * @var String
        */
        private $id;

        /**
        * Unixtimestamp, start time method
        * @var Int
        */
        private $stime;

        /**
        * @var WPAdm_Queue
        */
        private $queue;

        /**
        * @var string
        */
        private $dir;

        /**
        * @var string
        */
        private $tmp_dir;

        /**
        * type of backup 
        * @var string
        */
        private $type = 'full';

        private $name = '';

        public function __construct($params) {
            parent::__construct($params);
            $this->init(
            array(
            'id' => uniqid('wpadm_method_backup__'),
            'stime' => time(),
            )
            );


            $name = get_option('siteurl');

            $name = str_replace("http://", '', $name);
            $name = str_replace("https://", '', $name);
            $name = preg_replace("|\W|", "_", $name);
            $name .= '-' . $this->type . '-' . date("Y_m_d_H_i");
            $this->name = $name;

            // folder for backup
            $this->dir = DROPBOX_BACKUP_DIR_BACKUP . '/' . $this->name;
            $error = WPAdm_Core::mkdir(DROPBOX_BACKUP_DIR_BACKUP);
            if (!empty($error)) {
                $this->result->setError($error);
                $this->result->setResult(WPAdm_Result::WPADM_RESULT_ERROR);
            }
            $error = WPAdm_Core::mkdir($this->dir);
            if (!empty($error)) {
                $this->result->setError($error);
                $this->result->setResult(WPAdm_Result::WPADM_RESULT_ERROR);
            }
        }

        public function getResult()
        {
            $errors = array();

            $this->result->setResult(WPAdm_Result::WPADM_RESULT_SUCCESS);
            $this->result->setError('');

            WPAdm_Core::log( __('Start backup','dropbox-backup') );

            # create db dump
            WPAdm_Core::log( __('Start create db dump','dropbox-backup') );
            $error = WPAdm_Core::mkdir(DROPBOX_BACKUP_DIR_BACKUP);
            if (!empty($error)) {
                $this->result->setError($error);
                $this->result->setResult(WPAdm_Result::WPADM_RESULT_ERROR);
                return $this->result;
            }
            $mysql_dump_file = DROPBOX_BACKUP_DIR_BACKUP . '/mysqldump.sql';
            if (file_exists($mysql_dump_file)) {
                unlink($mysql_dump_file);
            }
            $wp_mysql_params = $this->getWpMysqlParams();

            if (isset($this->params['optimize']) && ($this->params['optimize']==1)) {
                WPAdm_Core::log( __('Table optimization','dropbox-backup') );
                $commandContext = new WPAdm_Command_Context();
                $commandContext ->addParam('command','mysqloptimize')
                ->addParam('host', $wp_mysql_params['host'])
                ->addParam('db', $wp_mysql_params['db'])
                ->addParam('user', $wp_mysql_params['user'])
                ->addParam('password', $wp_mysql_params['password']);
                $this->queue->clear()
                ->add($commandContext);
                unset($commandContext);
            }

            $commandContext = new WPAdm_Command_Context();
            $commandContext ->addParam('command','mysqldump')
            ->addParam('host', $wp_mysql_params['host'])
            ->addParam('db', $wp_mysql_params['db'])
            ->addParam('user', $wp_mysql_params['user'])
            ->addParam('password', $wp_mysql_params['password'])
            ->addParam('tables', '')
            ->addParam('to_file', $mysql_dump_file);
            $res = $this->queue->add($commandContext)
            ->save()
            ->execute();
            if (!$res) {
                $log = str_replace('%s', $this->queue->getError(), __('Error: Dump of Database wasn\'t created (%s)','dropbox-backup') );
                WPAdm_Core::log($log);
                $errors[] = $log;
            } elseif (0 == (int)@filesize($mysql_dump_file)) {
                $errors[] = __('MySQL Error: Database-Dump File is empty','dropbox-backup');
                WPAdm_Core::log(__('Dump of Database wasn\'t created (File of Database-Dump is empty!)','dropbox-backup'));
            } else {
                $size_dump = round( (filesize($mysql_dump_file) / 1024 / 1024) , 2);
                $log = str_replace("%s", $size_dump , __('Database Dump was successfully created ( %s Mb) : ','dropbox-backup') ) ;
                WPAdm_Core::log($log . $mysql_dump_file);
            }
            unset($commandContext);


            #ЗАРХИВИРУЕМ ФАЙЛЫ
            WPAdm_Core::log( __('Create a list of files for Backup','dropbox-backup') );
            $files = $this->createListFilesForArchive();
            if (file_exists($mysql_dump_file) && filesize($mysql_dump_file) > 0) {
                $files[] = $mysql_dump_file;
            }

            if (empty($files)) {
                $errors[] = __('Error: the list of Backup files is empty','dropbox-backup');
            }

            // split the file list by 170kbayt lists, To break one big task into smaller
            $files2 = array();
            $files2[0] = array();
            $i = 0;
            $size = 0;
            foreach($files as $f) {
                if ($size > 170000) {//~170kbyte
                    $i ++;
                    $size = 0;
                    $files2[$i] = array();
                }
                $f_size =(int)filesize($f);
                if ($f_size == 0 || $f_size > 1000000) {
                    WPAdm_Core::log('file '. $f .' size ' . $f_size);
                }
                $size += $f_size;
                $files2[$i][] = $f;
            }

            WPAdm_Core::log( __('List of Backup-Files was successfully created','dropbox-backup') );

            $this->queue->clear();

            foreach($files2 as $files) {
                $commandContext = new WPAdm_Command_Context();
                $commandContext ->addParam('command','archive')
                ->addParam('files', $files)
                ->addParam('to_file', $this->dir . '/'.$this->name)
                ->addParam('max_file_size', 900000)
                ->addParam('remove_path', ABSPATH);

                $this->queue->add($commandContext);
                unset($commandContext);
            }
            WPAdm_Core::log( __('Backup of Files was started','dropbox-backup')  );
            $this->queue->save()
            ->execute();
            WPAdm_Core::log( __('End of File Backup','dropbox-backup') );

            $files = glob($this->dir . '/'.$this->name . '*');
            $urls = array();
            $totalSize = 0;
            foreach($files as $file) {
                $urls[] = str_replace(ABSPATH, '', $file);
                $totalSize += @intval( filesize($file) );
            }
            $this->result->setData($urls);
            $this->result->setSize($totalSize);


            $remove_from_server = 0;
            if (isset($this->params['storage'])) {
                foreach($this->params['storage'] as $storage) {
                    if ($storage['type'] == 'ftp') {
                        WPAdm_Core::log( __('Begin copying files to FTP','dropbox-backup') );
                        $this->queue->clear();
                        $files = glob($this->dir . '/'.$this->name . '*');
                        //$this->getResult()->setData($files);
                        $ad = $storage['access_details'];
                        $dir = (isset($ad['dir'])) ? $ad['dir'] : '/';
                        $dir = trim($dir, '/') . '/' . $this->name;
                        foreach($files as $file) {
                            $commandContext = new WPAdm_Command_Context();
                            $commandContext ->addParam('command','send_to_ftp')
                            ->addParam('file', $file)
                            ->addParam('host', $ad['host'])
                            ->addParam('port', (isset($ad['port']))? $ad['port'] : 21)
                            ->addParam('user', $ad['user'])
                            ->addParam('password', $ad['password'])
                            ->addParam('dir', $dir)
                            ->addParam('http_host', isset($ad['http_host']) ? $ad['http_host'] : '');
                            $this->queue->add($commandContext);
                            unset($commandContext);
                        }
                        $res = $this->queue->save()
                        ->execute();
                        if (!$res) {
                            $log = __('FTP: ' ,'dropbox-backup');
                            WPAdm_Core::log($log . $this->queue->getError());
                            $errors[] = $log . $this->queue->getError();
                        }
                        WPAdm_Core::log( __('Finished copying files to FTP' ,'dropbox-backup') );
                        if (isset($storage['remove_from_server']) && $storage['remove_from_server'] == 1 ) {
                            $remove_from_server = $storage['remove_from_server'];
                        }
                    } elseif ($storage['type'] == 's3') {
                        WPAdm_Core::log( __('Begin coping files to S3' ,'dropbox-backup') );
                        $this->queue->clear();
                        $files = glob($this->dir . '/'.$this->name . '*');
                        //$this->getResult()->setData($files);
                        $ad = $storage['access_details'];
                        $dir = (isset($ad['dir'])) ? $ad['dir'] : '/';
                        $dir = trim($dir, '/') . '/' . $this->name;
                        foreach($files as $file) {
                            $commandContext = new WPAdm_Command_Context();
                            $commandContext ->addParam('command','send_to_s3')
                            ->addParam('file', $file)
                            ->addParam('bucket', $ad['bucket'])
                            ->addParam('AccessKeyId', $ad['AccessKeyId'])
                            ->addParam('SecretAccessKey', $ad['SecretAccessKey'])
                            ->addParam('SessionToken', $ad['SessionToken']);
                            $this->queue->add($commandContext);
                            unset($commandContext);
                        }
                        $res = $this->queue->save()
                        ->execute();
                        if (!$res) {
                            WPAdm_Core::log('S3: ' . $this->queue->getError());
                            $errors[] = 'S3: '.$this->queue->getError();
                        }
                        WPAdm_Core::log( __('Finished copying files to S3' ,'dropbox-backup') );
                        if (isset($storage['remove_from_server']) && $storage['remove_from_server'] == 1 ) {
                            $remove_from_server = $storage['remove_from_server'];
                        }
                    }
                }
                if ($remove_from_server) {
                    // удаляем файлы на сервере
                    WPAdm_Core::log( __('Remove the backup server' ,'dropbox-backup') );
                    WPAdm_Core::rmdir($this->dir);
                }

            }
            if (isset($this->params['gd']) && isset($this->params['gd']['key']) && isset($this->params['gd']['secret'])) {
                $this->queue->clear();
                $files = glob($this->dir . '/' . $this->name . '*');
                $files = array_merge_recursive(array($mysql_dump_file), $files);
                WPAdm_Core::log( __('files to google: ' ,'dropbox-backup') . print_r($files, true));
                $n = count($files);
                for($i = 0; $i <$n; $i++) {
                    $commandContext = new WPAdm_Command_Context();
                    $commandContext->addParam('command', 'send_to_google_drive')
                    ->addParam('key', $this->params['gd']['key'])
                    ->addParam('secret', $this->params['gd']['secret'])
                    ->addParam('token', $this->params['gd']['token'])
                    ->addParam('folder_project', $this->params['gd']['folder'])
                    ->addParam('folder', $this->name )
                    ->addParam('files', $files[$i]);
                    $this->queue->add($commandContext);
                    unset($commandContext);
                }
                $res = $this->queue->save()
                ->execute();
                if (!$res) {
                    WPAdm_Core::log( __('Google drive: ' ,'dropbox-backup') . $this->queue->getError());
                }
                //WPAdm_Core::log('google drive' . print_r($this->params, true));
            }
            if (isset($this->params['dropbox']) && isset($this->params['dropbox']['key']) && isset($this->params['dropbox']['secret'])) {
                $this->queue->clear();
                $files = glob($this->dir . '/' . $this->name . '*');
                $files = array_merge_recursive(array($mysql_dump_file), $files);
                WPAdm_Core::log( __('files to dropbox: ' ,'dropbox-backup') . print_r($files, true));
                $n = count($files);
                for($i = 0; $i <$n; $i++) {
                    $commandContext = new WPAdm_Command_Context();
                    $commandContext->addParam('command', 'send_to_dropbox')
                    ->addParam('key', $this->params['dropbox']['key'])
                    ->addParam('secret', $this->params['dropbox']['secret'])
                    ->addParam('token', $this->params['dropbox']['token'])
                    ->addParam('folder_project', $this->params['dropbox']['folder'])
                    ->addParam('folder', $this->name)
                    ->addParam('files', $files[$i]);
                    $this->queue->add($commandContext);
                    unset($commandContext);
                }
                $this->queue->save()
                ->execute();
                if (!$res) {
                    WPAdm_Core::log(__('Dropbox: ' ,'dropbox-backup') . $this->queue->getError());
                }
            }

            #Removing TMP-files
            WPAdm_Core::rmdir(DROPBOX_BACKUP_DIR_BACKUP . '/mysqldump.sql');

            #Removind old backups(if limit the number of stored backups)
            WPAdm_Core::log( __('Start removing old backups' ,'dropbox-backup') );
            if ($this->params['limit'] != 0) {
                $files = glob(DROPBOX_BACKUP_DIR_BACKUP . '/*');
                if (count($files) > $this->params['limit']) {
                    $files2 = array();
                    foreach($files as $f) {
                        $fa = explode('-', $f);
                        if (count($fa) != 3) {
                            continue;
                        }
                        $files2[$fa[2]] = $f;

                    }
                    ksort($files2);
                    $d = count($files2) - $this->params['limit'];
                    $del = array_slice($files2, 0, $d);
                    foreach($del as $d) {
                        WPAdm_Core::rmdir($d);
                    }
                }
            }
            WPAdm_Core::log( __('Finished removing old backups' ,'dropbox-backup') );

            WPAdm_Core::log( __('Creating a backup is completed' ,'dropbox-backup') );

            wpadm_class::setBackup(2);
            if (!empty($errors)) {
                $this->result->setError(implode("\n", $errors));
                $this->result->setResult(WPAdm_Result::WPADM_RESULT_ERROR);
                wpadm_class::setStatus(0);
                wpadm_class::setErrors( implode(", ", $errors) );
            } else {
                wpadm_class::setStatus(1);
            }
            wpadm_class::backupSend();
            return $this->result;
        }

        public function createListFilesForArchive() {
            $folders = array();
            $files = array();

            $files = array_merge(
            $files,
            array(
            ABSPATH . '.htaccess',
            ABSPATH . 'index.php',
            ABSPATH . 'license.txt',
            ABSPATH . 'readme.html',
            ABSPATH . 'wp-activate.php',
            ABSPATH . 'wp-blog-header.php',
            ABSPATH . 'wp-comments-post.php',
            ABSPATH . 'wp-config.php',
            ABSPATH . 'wp-config-sample.php',
            ABSPATH . 'wp-cron.php',
            ABSPATH . 'wp-links-opml.php',
            ABSPATH . 'wp-load.php',
            ABSPATH . 'wp-login.php',
            ABSPATH . 'wp-mail.php',
            ABSPATH . 'wp-settings.php',
            ABSPATH . 'wp-signup.php',
            ABSPATH . 'wp-trackback.php',
            ABSPATH . 'xmlrpc.php',
            )
            );

            if (!empty($this->params['minus-path'])) {
                $minus_path = explode(",", $this->params['minus-path']);
                foreach($files as $k => $v) {
                    $v = str_replace(ABSPATH  , '',  $v);
                    if (in_array($v, $minus_path)) {
                        unset($files[$k]);
                        WPAdm_Core::log( __('Skip file ' ,'dropbox-backup') . $v);
                    }
                }
            }

            $folders = array_merge(
            $folders,
            array(
            ABSPATH . 'wp-admin',
            ABSPATH . 'wp-content',
            ABSPATH . 'wp-includes',
            )
            );
            if (!empty($this->params['plus-path'])) {
                $plus_path = explode(",", $this->params['plus-path']);
                foreach($plus_path as $p) {
                    if (empty($p)) {
                        continue;
                    }
                    $p = ABSPATH . $p;
                    if (file_exists($p)) {
                        if (is_dir($p)) {
                            $folders[] = $p;
                        } else{
                            $files[] = $p;
                        }
                    }
                }
            }

            $folders = array_unique($folders);
            $files = array_unique($files);

            foreach($folders as $folder) {
                if (!is_dir($folder)) {
                    continue;
                }
                $files = array_merge($files, $this->directoryToArray($folder, true));
            }
            return $files;
        }


        private function directoryToArray($directory, $recursive) {
            $array_items = array();

            $d = str_replace(ABSPATH, '', $directory);
            // Skip dirs 
            $minus_path = explode(",", $this->params['minus-path']);
            if (in_array($d, $minus_path) ) {
                WPAdm_Core::log('Skip dir ' . $directory);
                return array();
            }

            $d = str_replace('\\', '/', $d);
            $tmp = explode('/', $d);
            $d1 = mb_strtolower($tmp[0]);
            unset($tmp[0]);
            $d2 = mb_strtolower(implode('/', $tmp));
            if (strpos($d2, 'cache') !== false && isset($tmp[0]) && !in_array($tmp[0], array('plugins', 'themes')) ) {
                WPAdm_Core::log('Skip dir(cache) ' . $directory);
                return array();
            }
            if(strpos($directory, DROPBOX_BACKUP_DIR_NAME) !== false) {
                return array();
            }

            if ($handle = opendir($directory)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        if (is_dir($directory. "/" . $file)) {
                            if($recursive) {
                                $array_items = array_merge($array_items, $this->directoryToArray($directory. "/" . $file, $recursive));
                            }

                            $file = $directory . "/" . $file;
                            if (!is_dir($file)) {
                                $ff = preg_replace("/\/\//si", "/", $file);
                                $f = str_replace(ABSPATH, '', $ff);
                                // skip "minus" dirs
                                if (!in_array($f, $minus_path)) {
                                    $array_items[] = $ff;
                                } else {
                                    WPAdm_Core::log('Skip file ' . $ff);
                                }
                            }
                        } else {
                            $file = $directory . "/" . $file;
                            if (!is_dir($file)) {
                                $ff = preg_replace("/\/\//si", "/", $file);
                                $f = str_replace(ABSPATH, '', $ff);
                                // skip "minus" dirs
                                if (!in_array($f, $minus_path)) {
                                    $array_items[] = $ff;
                                } else {
                                    WPAdm_Core::log( __('Skip dir ' ,'dropbox-backup') . $ff);
                                }
                            }
                        }
                    }
                }
                closedir($handle);
            }
            return $array_items;
        }


        /*
        * returns the elements of access to MySQL from WP options
        * return Array()
        */
        private function getWpMysqlParams()
        {
            $db_params = array(
            'password' => 'DB_PASSWORD',
            'db' => 'DB_NAME',
            'user' => 'DB_USER',
            'host' => 'DB_HOST',
            );

            $r = "/define\('(.*)', '(.*)'\)/";
            preg_match_all($r, file_get_contents( ABSPATH . "wp-config.php"), $m);
            $params = array_combine($m[1], $m[2]);
            foreach($db_params as $k=>$p) {
                $db_params[$k] = $params[$p];
            }
            return $db_params;
        }


        private function init(array $conf) {
            $this->id = $conf['id'];
            $this->stime = $conf['stime'];
            $this->queue = new WPAdm_Queue($this->id);
        }
    }
}
