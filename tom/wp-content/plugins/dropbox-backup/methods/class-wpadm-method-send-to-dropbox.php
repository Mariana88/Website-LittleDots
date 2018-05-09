<?php
/**
* Бэкап сайта
* Class WPadm_Method_Send_To_Dropbox
*/
if (!class_exists('WPadm_Method_Send_To_Dropbox')) {
    class WPadm_Method_Send_To_Dropbox extends WPAdm_Method_Class {
        /**
        * @var WPAdm_Queue
        */
        private $queue;

        private $id;

        //private $name = '';

        public function getResult()
        {
            $errors = array();
            $this->id = uniqid('wpadm_method_send_to_dropbox_');

            $this->result->setResult(WPAdm_Result::WPADM_RESULT_SUCCESS);
            $this->result->setError('');
            if (isset($this->params['local']) && $this->params['local']) {
                $params_data_cron = WPAdm_Running::getCommandResultData('local_backup');
                $this->params['files'] = $params_data_cron['data'];
                $this->params['access_details']['dir'] = $params_data_cron['name'];
                WPAdm_Core::log( '' );
            }


            $this->queue = new WPAdm_Queue($this->id);

            $ad = $this->params['access_details'];
            $this->queue->clear();
            $files = $this->params['files'];
            //$this->getResult()->setData($files);

            $dir = (isset($ad['dir'])) ? $ad['dir'] : '/';
            //$dir = trim($dir, '/') . '/' . $this->name;
            if (is_array($files)) {
                $send = false;
                
                foreach($files as $file) {
                    if (isset($this->params['local']) && $this->params['local']) {
                        $data_command = WPAdm_Running::getCommandResultData('command_dropbox');
                        $data_error_command = WPAdm_Running::getCommandResultData('errors_sending');
                        if (isset($data_error_command[ABSPATH . $file]) && $data_error_command[ABSPATH . $file]['count'] > WPADM_COUNT_LIMIT_SEND_TO_DROPBOX) {
                            $msg = str_replace('%file%', $file, __('Not perhaps sending file: "%file%". If you wish make upload file, increase execution time code or speed internet provider is small for upload file.' ,'dropbox-backup'));
                            WPAdm_Core::log( $msg );
                            $errors[] = $msg;
                            break;
                        }
                        if ( !empty($data_command) && in_array(ABSPATH . $file, $data_command) ) {
                            continue;
                        }
                    }
                    $commandContext = new WPAdm_Command_Context();
                    $commandContext->addParam('command', 'send_to_dropbox')
                    ->addParam('key', $ad['key'])
                    ->addParam('secret', $ad['secret'])
                    ->addParam('token', $ad['token'])
                    ->addParam('folder_project', $ad['folder'])
                    ->addParam('folder', $dir)
                    ->addParam('files', ABSPATH . $file);
                    if (isset($this->params['local']) && $this->params['local']) {
                        $commandContext->addParam('local', true);
                    }
                    $this->queue->add($commandContext);
                    unset($commandContext);
                    $send = true;
                }
            }
            if ($send) {
                WPAdm_Core::log( __('Start copy to Dropbox Cloud' ,'dropbox-backup') );
                $res = $this->queue->save()->execute();
                WPAdm_Core::log( __('End Copy Files to Dropbox' ,'dropbox-backup') );
            }
            if (isset($res) && !$res) {
                WPAdm_Core::log(__('Answer from Dropbox ' ,'dropbox-backup') . $this->queue->getError());
                $errors[] = __('Answer from Dropbox ' ,'dropbox-backup') . $this->queue->getError();
            }
            
            if (count($errors) > 0) {
                $this->result->setError(implode("\n", $errors));
                $this->result->setResult(WPAdm_Result::WPADM_RESULT_ERROR);
            } else { 
                if (class_exists('wpadm_wp_full_backup_dropbox') && !file_exists( WPAdm_Core::getTmpDir() . "/notice-star") ) {
                    wpadm_wp_full_backup_dropbox::setFlagToTmp( 'notice-star', time() . "_1d" );
                }
                if (isset($this->params['local']) && $this->params['local'] && isset($params_data_cron)) {
                    $this->result->setData($this->params['files']);
                    $this->result->setSize($params_data_cron['size']);
                    $this->result->setValue('md5_data', md5 ( print_r($this->result->toArray(), 1 ) ) );
                    $this->result->setValue('name', $params_data_cron['name']);
                    $this->result->setValue('time', $params_data_cron['time']);
                    $this->result->setValue('type', 'dropbox');
                    $this->result->setValue('counts', $params_data_cron['counts'] );
                    if( (isset($this->params['is_local_backup']) && $this->params['is_local_backup'] == 0 ) || ( !isset($this->params['is_local_backup']) ) ) {
                        WPAdm_Core::rmdir( DROPBOX_BACKUP_DIR_BACKUP . "/{$params_data_cron['name']}");
                    }
                }
            }

            return $this->result;
        }

        private function init(array $conf) {
            //todo: нормализация
            $this->id = $conf['id'];
            $this->stime = $conf['stime'];
            $this->queue = new WPAdm_Queue($this->id);
            $this->type = $conf['type'];
        }
    }
}