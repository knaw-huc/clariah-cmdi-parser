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
To be done

## <a name="class"></a>Classes
To be done

## <a name="tweak"></a>Tweaker
To be done

## <a name="conf"></a>Configuration
To be done
