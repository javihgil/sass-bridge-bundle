# SassBridgeBundle

# Configuración de filtro SassRewrite de Assetic

**app/config/config.yml**

    # Assetic Configuration
    assetic:
        ...
        filters:
            sassrewrite:
                resource: %kernel.root_dir%/../src/Jhg/SassBridgeBundle/Resources/config/assetic/sassrewrite.xml
                apply_to: "\.(scss)$"
            ...

Hay que apuntar al XML del filtro en *resource* según el directorio de instalación.

**Usage**

    /* src/....Bundle/Resources/assets/styles/sample.scss */

    @import '@OtherBundleBundle/Resources/assets/styles/other-sample.scss';

    div#id {
    	background-image: url('@OtherBundleBundle/Resources/public/images/sample.png');
    }


# Configure Sass

## Ubuntu installation

    $ sudo apt-get install ruby
    $ sudo gem install sass
    $ sudo gem install compass

## Symfony config

**config/parameters.yml**

    assetic_ruby_bin: /usr/bin/ruby
    assetic_compass_bin: /usr/local/bin/compass
    assetic_sass_bin: /usr/local/bin/sass

**config/config.yml**
    assetic:
        ....
        ruby: "%assetic_ruby_bin%"
        filters:
            sass:
                bin: "%assetic_compass_bin%"
            compass:
                bin: "%assetic_compass_bin%"

# Bootstrap Sass

**composer.json**

    "require": {
        "twbs/bootstrap-sass": "dev-master"
    },

**app/config/config.yml**

    jhg_sass_bridge:
        resources_paths:
            bootstrap: "../vendor/twbs/bootstrap-sass/vendor/assets/stylesheets/bootstrap"

## Usage

    @import '@boostrap/variables';

