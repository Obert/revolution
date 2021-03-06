<?php
/**
 * Loads the workspace manager
 *
 * @package modx
 * @subpackage manager.workspaces
 */
class WorkspacesManagerController extends modManagerController {
    public $templateFile = 'workspaces/index.tpl';
    public $providerId = 1;
    public $providerName = 'modx.com';
    /**
     * Check for any permissions or requirements to load page
     * @return bool
     */
    public function checkPermissions() {
        return $this->modx->hasPermission('workspaces');
    }

    /**
     * Register custom CSS/JS for the page
     * @return void
     */
    public function loadCustomCssJs() {
        $mgrUrl = $this->modx->getOption('manager_url',null,MODX_MANAGER_URL);
        $this->addJavascript($mgrUrl.'assets/modext/core/modx.view.js');
        $this->addJavascript($mgrUrl.'assets/modext/widgets/core/modx.tree.checkbox.js');
        $this->addJavascript($mgrUrl.'assets/modext/widgets/core/modx.panel.wizard.js');
        $this->addJavascript($mgrUrl.'assets/modext/workspace/package.browser.js');
        $this->addJavascript($mgrUrl.'assets/modext/workspace/package.download.panel.js');
        $this->addJavascript($mgrUrl.'assets/modext/workspace/package.add.panel.js');
        $this->addJavascript($mgrUrl.'assets/modext/workspace/package.install.window.js');
        $this->addJavascript($mgrUrl.'assets/modext/workspace/package.uninstall.window.js');
        $this->addJavascript($mgrUrl.'assets/modext/workspace/package.update.window.js');
        $this->addJavascript($mgrUrl.'assets/modext/workspace/combos.js');
        $this->addJavascript($mgrUrl.'assets/modext/workspace/package.grid.js');
        $this->addJavascript($mgrUrl.'assets/modext/workspace/provider.grid.js');
        $this->addJavascript($mgrUrl.'assets/modext/workspace/workspace.panel.js');
        $this->addHtml('<script type="text/javascript">MODx.provider = "'.$this->providerId.'";MODx.providerName = "'.$this->providerName.'";</script>');
        $this->addJavascript($mgrUrl.'assets/modext/workspace/index.js');
    }

    /**
     * Custom logic code here for setting placeholders, etc
     * @param array $scriptProperties
     * @return mixed
     */
    public function process(array $scriptProperties = array()) {
        $placeholders = array();

        /* ensure directories for Package Management are created */
        $cacheManager = $this->modx->getCacheManager();
        $directoryOptions = array(
            'new_folder_permissions' => $this->modx->getOption('new_folder_permissions',null,0775),
        );
        $errors = array();

        /* create assets/ */
        $assetsPath = $this->modx->getOption('base_path').'assets/';
        if (!is_dir($assetsPath)) {
            $cacheManager->writeTree($assetsPath,$directoryOptions);
        }
        if (!is_dir($assetsPath) || !is_writable($assetsPath)) {
            $errors['assets_not_created'] = $this->modx->lexicon('dir_err_assets',array('path' => $assetsPath));
        }
        unset($assetsPath);

        /* create assets/components/ */
        $assetsCompPath = $this->modx->getOption('base_path').'assets/components/';
        if (!is_dir($assetsCompPath)) {
            $cacheManager->writeTree($assetsCompPath,$directoryOptions);
        }
        if (!is_dir($assetsCompPath) || !is_writable($assetsCompPath)) {
            $errors['assets_comp_not_created'] = $this->modx->lexicon('dir_err_assets_comp',array('path' => $assetsCompPath));
        }
        unset($assetsCompPath);

        /* create core/components/ */
        $coreCompPath = $this->modx->getOption('core_path').'components/';
        if (!is_dir($coreCompPath)) {
            $cacheManager->writeTree($coreCompPath,$directoryOptions);
        }
        if (!is_dir($coreCompPath) || !is_writable($coreCompPath)) {
            $errors['core_comp_not_created'] = $this->modx->lexicon('dir_err_core_comp',array('path' => $coreCompPath));
        }

        if (!empty($errors)) {
            $placeholders['errors'] = $errors;
            $this->templateFile = 'workspaces/error.tpl';
            return $placeholders;
        }

        $this->getDefaultProvider();

        return $placeholders;
    }

    /**
     * Get the default Provider for Package Management
     * 
     * @return modTransportProvider|void
     */
    public function getDefaultProvider() {
        $c = $this->modx->newQuery('transport.modTransportProvider');
        $c->where(array(
            'name:=' => 'modxcms.com',
            'OR:name:=' => 'modx.com',
        ));
        $provider = $this->modx->getObject('transport.modTransportProvider',$c);
        if ($provider) {
            $this->providerId = $provider->get('id');
            $this->providerName = $provider->get('name');
        } else {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'Could not find the main provider for some reason with a name of "modx.com". Did you delete it?');
        }
        return $provider;
    }

    /**
     * Return the pagetitle
     *
     * @return string
     */
    public function getPageTitle() {
        return $this->modx->lexicon('package_management');
    }

    /**
     * Return the location of the template file
     * @return string
     */
    public function getTemplateFile() {
        return $this->templateFile;
    }

    /**
     * Specify the language topics to load
     * @return array
     */
    public function getLanguageTopics() {
        return array('workspace','namespace');
    }
}