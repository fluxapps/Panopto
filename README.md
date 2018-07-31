Panopto
==========
### Description
This plugin can be installed in the LMS ILIAS to implement an interface to the external video platform Panopto. It introduces a new repository object type, in which video from Panopto can be added and viewed, as well as new videos can be recorded and  uploaded directly to Panopto.

### Installation
Start at your ILIAS root directory
```bash
mkdir -p Customizing/global/plugins/Services/Repository/RepositoryObject
cd Customizing/global/plugins/Services/Repository/RepositoryObject
git clone https://github.com/studer-raimann/Panopto.git
```

### Configuration
##### Panopto
Login to your Panopto instance as administrator. Navigate to "System" -> "Identity Providers" and add a new provider. Enter the following data:
* **Provider Type**: *BLTI*
* **Instance Name**: choose an identifier, e.g: "*ilias.myinstitution*" (will be needed in the plugin configuration)
* **Friendly Description**:	choose any description
* **Bounce Page URL**: http://{your-ilias-installation}/Customizing/global/plugins/Services/Repository/RepositoryObject/Panopto/classes/bounce.php
* **Parent folder name**: choose a folder, where all objects coming from this ILIAS instance will be created
* **Suppress access permission sync on LTI link**: Set 'true' if you want to stop the behavior to revoke Viewer permission of other course folders.
* **Application Key**: save this key for the plugin configuration
* **Bounce page blocks iframes**: False (don't check)
* **Default Sign-in Option**: False (don't check)
* **Personal folders for users**: Choose which kind of users should get personal folders (can be changed later)
* **LTI Username parameter override**:	Leave empty
* **Show this in Sign-in Dropdown**: False (don't check)

Now, to create an api user:
* Navigate to "System" -> "Users" 
* Click on "Batch Create" (for some reason you can't create single external users)
* As "Provider", choose the previously created identity provider
* Enter a username and an email address comma-separated, e.g. "api_user, example@myinstitution.com"
* Uncheck the checkbox "Create a personal folder for each user" (except if you want a personal folder for the api user for some reason)
* Click "Preview" and on the next Screen "Create Users"

##### ILIAS
Now, login to your ILIAS instance as an administrator. Navigate to "Administration" -> "Plugins" and look for the "Panopto" plugin. Install/Update the plugin if it's not up-to-date yet and afterwards choose "Configure". Configure the plugin as followed:
* **Object Title**: choose how this object type should be named in ILIAS (displayed e.g. when creating a new object in the repository)
* **API user**: enter the name of the previously created API user (e.g. "api_user")
* **Hostname**: the hostname of your Panopto instance without "https://". E.g. "demo.panopto.com"
* **Instance Name**: the same identifier you chose when creating the identity provider in Panopto
* **Application Key**: the key which appeared when creating the identity provider in Panopto
* **User Identification**: chose which user field will be used as user identification (either the login or the external account)


### ILIAS Plugin SLA

Wir lieben und leben die Philosophie von Open Soure Software! Die meisten unserer Entwicklungen, welche wir im Kundenauftrag oder in Eigenleistung entwickeln, stellen wir öffentlich allen Interessierten kostenlos unter https://github.com/studer-raimann zur Verfügung.

Setzen Sie eines unserer Plugins professionell ein? Sichern Sie sich mittels SLA die termingerechte Verfügbarkeit dieses Plugins auch für die kommenden ILIAS Versionen. Informieren Sie sich hierzu unter https://studer-raimann.ch/produkte/ilias-plugins/plugin-sla.

Bitte beachten Sie, dass wir nur Institutionen, welche ein SLA abschliessen Unterstützung und Release-Pflege garantieren.

### Contact
info@studer-raimann.ch  
https://studer-raimann.ch  