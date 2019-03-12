# CLARIAH CMDI Parser

## List of contents
1. [Introduction](#intro)
2. [CMDI profiles and tweaks](#prof) 
3. [Classes](#class)
4. [Tweaker](#tweak)
5. [Configuration](#conf)

## <a name="intro"></a>Introduction
The CLARIAH CMDI Parser is one of two components (the other one is the [HUC Generic Editor](https://github.com/knaw-huc/huc-generic-editor)) for adding a CMDI editor to your own website. 

For more information about the concept of the CLARIAH CMDI Editor, see [Tweak Your CMDI Forms to the Max (2018)](https://office.clarin.eu/v/CE-2018-1292-CLARIN2018_ConferenceProceedings.pdf#page=102) 

The CLARIAH CMDI Parser consists of two PHP classes, one, CcfParser, for parsing CMDI profiles and records to transform their respective XML structures into a JSON structure, that can be read by the HUC Generic Editor.

The other class, CcfRecord, transforms the edited data back into CMDI metadata records.

In addition to the data reading and writing functions of these PHP classes, there is also the possibility of tweaking the CMDI profiles to enhance usability, for instance by creating human readable field labels, supporting multiple language versions and performing inline validation.

For testing purposes it is possible to download a host application, CLARIAH CMDI Forms, in which the CLARIAH CMDI Parser and the HUC Generic Editor are integrated. For this application, see https://github.com/knaw-huc/clariah-cmdi-forms

## <a name="prof"></a>CMDI profiles and tweaks
The structure of a class of CMDI metadata records is defined in a corresponding CMDI Profile (for more detailed information about CMDI, see [Component Metadata](https://www.clarin.eu/content/component-metadata)).

The CLARIAH CMDI Forms editor (CCFeditor) is capable to generate a form for CMDI metadata records based on any CMDI profile. In fact, it uses the profile as a set of definitions for creating a specific metadata form. In addition a tweak file with the same XML structure can be used to enhance the usability of the form, as mentioned in the previous paragraph.

The next example consists of a fragment of a CMDI profile, followed by a theak file for the same profile. The tweak consists of the same fragment, but with Dutch labels and an autovalue definition..

### Example 1: profile fragment

```xml
<Element name="provenance" ConceptLink="http://purl.org/dc/terms/provenance" ValueScheme="string" CardinalityMin="0" CardinalityMax="unbounded" Multilingual="true"/>
 <Element name="shortTitle" ValueScheme="string" CardinalityMin="0" CardinalityMax="unbounded" Multilingual="true"/>
            <Element name="storageLocation" ValueScheme="string" CardinalityMin="0" CardinalityMax="1" Multilingual="true"/>
            <Element name="web" ValueScheme="boolean" CardinalityMin="0" CardinalityMax="1"/>
            <Element name="category" ConceptLink="http://hdl.handle.net/11459/CCR_C-3646_60ef52ab-b400-cb07-7cc2-bda80ec72001 " CardinalityMin="0" CardinalityMax="1">
                <ValueScheme>
                    <Vocabulary>
                        <enumeration>
                            <item ConceptLink="" AppInfo="">MI General</item>
                            <item ConceptLink="" AppInfo="">Dialectology/language variation</item>
                            <item ConceptLink="" AppInfo="">Ethnology</item>
                            <item ConceptLink="" AppInfo="">Ethnology: Dutch song</item>
                            <item ConceptLink="" AppInfo="">Onomastics</item>
                            <item ConceptLink="" AppInfo="">Library</item>
                            <item ConceptLink="" AppInfo="">Realia</item>
                        </enumeration>
                    </Vocabulary>
                </ValueScheme>
            </Element>
```

### Example 1: tweak file fragment

```xml
<Element name="provenance" cue:hide="yes" xmlns:cue="http://www.clarin.eu/cmdi/cues/1">
                <clariah:label xml:lang="nl">Herkomst</clariah:label>
                <AutoValue>default:Meertens Instituut</AutoValue>
            </Element>
            <Element name="shortTitle">
                <clariah:label xml:lang="nl">Titel kort</clariah:label>
            </Element>
            <Element name="storageLocation">
                <clariah:label xml:lang="nl">Opslaglocatie</clariah:label>
            </Element>
            <Element name="category">
                <clariah:label xml:lang="nl">Categorie</clariah:label>
                <ValueScheme>
                    <Vocabulary>
                        <enumeration>
                            <item><clariah:label xml:lang="nl">MI Algemeen</clariah:label><clariah:value>MI General</clariah:value></item>
                            <item><clariah:label xml:lang="nl">Dialectologie/taalvariatie</clariah:label><clariah:value>Dialectology/language variation</clariah:value></item>
                            <item><clariah:label xml:lang="nl">Etnologie</clariah:label><clariah:value>Ethnology</clariah:value></item>
                            <item><clariah:label xml:lang="nl">Etnologie: Lied</clariah:label><clariah:value>Ethnology: Dutch song</clariah:value></item>
                            <item><clariah:label xml:lang="nl">Naamkunde</clariah:label><clariah:value>Onomastics</clariah:value></item>
                            <item><clariah:label xml:lang="nl">Bibliotheek</clariah:label><clariah:value>Library</clariah:value></item>
                            <item>Realia</item>
                        </enumeration>
                    </Vocabulary>
                </ValueScheme>
            </Element>
```



## <a name="class"></a>Classes
The two classes of the CLARIAH CMDI Parser are:
1. CcfParser class (CcfParser.class.php)
2. CcfRecord class (CcfRecord.class.php)

In short, the first class parses a CMDI profile and optional metadata record and/or tweak file, to create a JSON structure for the Generic Editor. The second class builds a new CMDI record from new or edited data.

The __CcfParser class__ can be invoked in PHP code as follows:

```php
$parser = new Ccfparser();
```
This class contains one method: ```parseTweak()```. The syntax is 

```
parseTweak(string  <cmdi_profile_name>, string <tweak_file_name>, string <tweaker_file_name>, string <cmdi_record_name>) : string <JSON structure>;
```

__Parameters:__

*<cmdi_profile_name>* : Path and filename of a CMDI Profile file.

*<tweak_file_name>* : Path and file name of the tweak file (see previous paragraph). 

*<tweaker_file_name>* : Path and file name of the tweaker file, a xslt stylesheet for merging the CMDI profile and the tweak into one XML file. This XML file contains the field and component definition for the editor. Both CMDI 1.1. and 1.2 are supported.

*<cmdi_record_name>* : Path and file name of the CMDI record, in case of editing an existing record.

__Examples:__

Create new record, without tweaks.
```php
$parser = new Ccfparser();
$json_struc = $parser->parseTweak($cmdi_profile, null, null, null);
```

Edit record, without tweaks.
```php
$parser = new Ccfparser();
$json_struc = $parser->parseTweak($cmdi_profile, null, null, $cmdi_record);
```

Edit record, with tweaks.
```php
$parser = new Ccfparser();
$json_struc = $parser->parseTweak($cmdi_profile, $tweak_file, $tweaker, $cmdi_record);
```


The __CcfRecord class__ is used to (re)create a CMDI record file, based on the JSON structure from the editor, containing the edited data. This class can be invoked by:

```php
$record = new Ccfrecord();
```

This class also contains only one method:

```
createComponents(string <input_components>, string <profile_ID>, XML <cmdi_record>, string <resource_path>, string <upload_path>) : XML <cmdiRecord>;
```

__Parameters:__

*<input_components>* : JSON string from editor, contaning CMDI record data.

*<profile_ID>* : ID of the CMDI profile. Is extracted form the CMDI profile.

*<cmdi_record>* : Path and file name of CMDI record. In case of editing an existing record the file of the record in question is used. For new records a minimal record template is used.

*<resource_path>* : Destination path for uploaded resources that belong to the CMDI record.

*<upload_path>* : Location on the server where the resources are stored afer the upload process.

__Examples__

Edit with uploading resources:

```php
$record = new Ccfrecord();
$record->createComponents($json_from_editor, $profile_id, $cmdi_record, $resource_path, UPLOAD_PATH);
```

Edit without resources:

```php
$record = new Ccfrecord();
$record->createComponents($json_from_editor, $profile_id, $cmdi_record, null, null);
```


## <a name="tweak"></a>Tweaker
The tweaker  ```tweaker/mergeTweak.xsl``` is used by CcfParser class. If nullifiedd in the method call ```Ccfparser()->parseTweak()``` the editor will be build on basis of the raw CMDI profile, without tweaks.
