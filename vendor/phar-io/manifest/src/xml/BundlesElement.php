<?php
/**
 *
 * PHP version 5 and 7
 *
 * @author Qordoba Team <support@qordoba.com>
 * @copyright 2018 Qordoba Team
 *
 */

namespace PharIo\Manifest;

class BundlesElement extends ManifestElement {
    public function getComponentElements() {
        return new ComponentElementCollection(
            $this->getChildrenByName('component')
        );
    }
}
