Panopto
==========
## Description
This plugin can be installed in the LMS ILIAS to implement an interface to the external video platform Panopto. It introduces a new repository object type, in which video from Panopto can be added and viewed, as well as new videos can be recorded and  uploaded directly to Panopto.

## Installation
Start at your ILIAS root directory
```bash
mkdir -p Customizing/global/plugins/Services/Repository/RepositoryObject
cd Customizing/global/plugins/Services/Repository/RepositoryObject
git clone https://github.com/fluxapps/Panopto.git
```

## Configuration
### Panopto
Login to your Panopto instance as administrator. 

#### Identity Provider
Navigate to "System" -> "Identity Providers" and add a new provider. Enter the following data:
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

After the user is created, open the user details by clicking on the user's name. Check the role "Administrator" under "Info" -> "System Roles" and click "Update Roles".

##### REST Client
Navigate to "System" -> "API Clients" and add a new client. Enter a Client Name of your choice and select the Client Type "User Based Server Application". All other fields can be left empty. Write down the Client Name, Client ID and Client Secret for later.

Unfortunately, the previously created api user can not be used for the REST api, as it has to be an internal user. So create another user:
* Navigate to "System" -> "Users"
* Click on "New"
* Fill out the form as follows:
    * enter the required fields 
    * write down the username and password for later
    * uncheck the Options "Email user when recorded..." and "Create a personal folder..."
* Create the user

#### ILIAS

Now, login to your ILIAS instance as an administrator. Navigate to "Administration" -> "Plugins" and look for the "Panopto" plugin. Install/Update the plugin if it's not up-to-date yet and afterwards choose "Configure". Configure the plugin as followed:
* **General**
    * **Object Title**: choose how this object type should be named in ILIAS (displayed e.g. when creating a new object in the repository)
* **SOAP API**
    * **API user**: enter the name of the previously created API user (e.g. "api_user")
    * **Hostname**: the hostname of your Panopto instance without "https://". E.g. "demo.panopto.com"
    * **Instance Name**: the same identifier you chose when creating the identity provider in Panopto
    * **Application Key**: the key which appeared when creating the identity provider in Panopto
    * **User Identification**: chose which user field will be used as user identification (either the login or the external account)
* **REST API**
    * **API User**: the user created in the section [REST Client](#rest-client)
    * **API Password**: the password for this user
    * **Client Name**: name of REST Client created in the section [REST Client](#rest-client)
    * **Client ID**: ID of REST Client created in the section [REST Client](#rest-client)
    * **Client-Secret**: Secret of REST Client created in the section [REST Client](#rest-client)

## Authors

This is an OpenSource project by studer + raimann ag (https://fluxlabs.ch)

## License

This project is licensed under the GPL v3 License

## Contributing :purple_heart:
Please ...
1. ... register an account at https://git.fluxlabs.ch
2. ... write us an email: support@fluxlabs.ch
3. ... we give you access to the projects you like to contribute :fire:


## Adjustment suggestions / bug reporting :feet:
Please ...
1. ... register an account at https://git.fluxlabs.ch
2. ... ask us for a sla: support@fluxlabs.ch :kissing_heart:
3. ... we will give you the access with the possibility to read and create issues or to discuss feature requests with us.

