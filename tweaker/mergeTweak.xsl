<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns:clariah="http://www.clariah.eu/" 
    xmlns:cue="http://www.clarin.eu/cmdi/cues/1"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    
    <xsl:param name="tweakFile"/>
    <xsl:variable name="tweak" select="document($tweakFile)"/>
    
    <xsl:template match="node() | @*">
        <xsl:copy>
            <xsl:apply-templates select="@* | node()"/>
        </xsl:copy>
    </xsl:template>
    
    <xsl:template match="ComponentSpec/@xsi:*"/>
    
    <xsl:template match="/ComponentSpec">
        <xsl:if test="@isProfile!='true'">
            <xsl:message terminate="yes">ERR: only profile specifications can be processed by CCF!</xsl:message>
        </xsl:if>
        <xsl:if test="@CMDVersion!='1.2'">
            <xsl:message terminate="yes">ERR: only CMD 1.2 profile specifications can be processed by CCF!</xsl:message>
        </xsl:if>
        <xsl:if test="not($tweak/ComponentSpec)">
            <xsl:message terminate="yes">ERR: only CMD tweaks can be processed by CCF!</xsl:message>
        </xsl:if>
        <ComponentSpec xmlns:clariah="http://www.clariah.eu/">
            <xsl:apply-templates select="node() | @*">
                <xsl:with-param name="tweak" select="$tweak/ComponentSpec"></xsl:with-param>
            </xsl:apply-templates>
        </ComponentSpec>
    </xsl:template>
    
    <xsl:template match="Header">
        <xsl:param name="tweak"/>
        <xsl:variable name="myTweak" select="$tweak/Header"/>
        <xsl:copy>
            <xsl:apply-templates select="node() | @*">
                <xsl:with-param name="tweak" select="$myTweak"></xsl:with-param>
            </xsl:apply-templates>
            <xsl:apply-templates select="$myTweak/clariah:*" mode="copy"/>
        </xsl:copy>
    </xsl:template>
    
    <xsl:template match="ID">
        <xsl:param name="tweak"/>
        <xsl:variable name="myTweak" select="$tweak/ID"/>
        <xsl:if test=".!=$myTweak">
            <xsl:message terminate="yes">ERR: the tweak is for another profile! (<xsl:value-of select="$myTweak"/>!=<xsl:value-of select="."/>)</xsl:message>
        </xsl:if>
        <xsl:copy>
            <xsl:apply-templates select="node() | @*">
                <xsl:with-param name="tweak" select="$myTweak"/>
            </xsl:apply-templates>
        </xsl:copy>
    </xsl:template>
    
    <xsl:template match="Component">
        <xsl:param name="tweak"/>
        <xsl:variable name="myTweak" select="$tweak/Component[@name=current()/@name]"/>
        <xsl:copy>
            <xsl:apply-templates select="@*" mode="cmd">
                <xsl:with-param name="tweak" select="$myTweak"/>
            </xsl:apply-templates>
            <xsl:apply-templates select="@cue:*" mode="cue">
                <xsl:with-param name="tweak" select="$myTweak"/>
            </xsl:apply-templates>
            <xsl:apply-templates select="$myTweak/@cue:*" mode="tweak-cue">
                <xsl:with-param name="prof" select="."/>
            </xsl:apply-templates>
            <xsl:apply-templates select="$myTweak/clariah:*" mode="copy"/>
            <xsl:apply-templates select="node()">
                <xsl:with-param name="tweak" select="$myTweak"/>
            </xsl:apply-templates>
        </xsl:copy>
    </xsl:template>
    
    <xsl:template match="Element">
        <xsl:param name="tweak"/>
        <xsl:variable name="myTweak" select="$tweak/Element[@name=current()/@name]"/>
        <xsl:copy>
            <xsl:apply-templates select="@*" mode="cmd">
                <xsl:with-param name="tweak" select="$myTweak"/>
            </xsl:apply-templates>
            <xsl:apply-templates select="@cue:*" mode="cue">
                <xsl:with-param name="tweak" select="$myTweak"/>
            </xsl:apply-templates>
            <xsl:apply-templates select="$myTweak/@cue:*" mode="tweak-cue">
                <xsl:with-param name="prof" select="."/>
            </xsl:apply-templates>
            <xsl:apply-templates select="$myTweak/clariah:*" mode="copy"/>
            <xsl:apply-templates select="$myTweak/AutoValue" mode="copy"/>
            <xsl:if test="@ValueScheme='string' or normalize-space(@ValueScheme='') and not(ValueScheme) and $myTweak/ValueScheme">
                <xsl:apply-templates select="$myTweak/ValueScheme" mode="copy"/>
            </xsl:if>
            <xsl:apply-templates select="node()">
                <xsl:with-param name="tweak" select="$myTweak"/>
            </xsl:apply-templates>
        </xsl:copy>
    </xsl:template>
    
    <xsl:template match="Attribute">
        <xsl:param name="tweak"/>
        <xsl:variable name="myTweak" select="$tweak/Attribute[@name=current()/@name]"/>
        <xsl:copy>
            <xsl:apply-templates select="@*" mode="cmd">
                <xsl:with-param name="tweak" select="$myTweak"/>
            </xsl:apply-templates>
            <xsl:apply-templates select="@cue:*" mode="cue">
                <xsl:with-param name="tweak" select="$myTweak"/>
            </xsl:apply-templates>
            <xsl:apply-templates select="$myTweak/@cue:*" mode="tweak-cue">
                <xsl:with-param name="prof" select="."/>
            </xsl:apply-templates>
            <xsl:apply-templates select="$myTweak/clariah:*" mode="copy"/>
            <xsl:if test="@ValueScheme='string' or normalize-space(@ValueScheme='') and not(ValueScheme) and $myTweak/ValueScheme">
                <xsl:apply-templates select="$myTweak/ValueScheme" mode="copy"/>
            </xsl:if>
            <xsl:apply-templates select="node()">
                <xsl:with-param name="tweak" select="$myTweak"/>
            </xsl:apply-templates>
        </xsl:copy>
    </xsl:template>
    
    <xsl:template match="ValueScheme">
        <xsl:param name="tweak"/>
        <xsl:variable name="myTweak" select="$tweak/ValueScheme"/>
        <xsl:choose>
            <xsl:when test="$myTweak">
                <xsl:apply-templates select="$myTweak" mode="copy"/>                
            </xsl:when>
            <xsl:otherwise>
                <xsl:copy>
                    <xsl:apply-templates select="@* | node()"/>
                </xsl:copy>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    

    
    
    <!-- add clariah:value wrapper to items from the profile -->
    <xsl:template match="item">
        <xsl:copy>
            <xsl:apply-templates select="@*"/>
            <clariah:value>
                <xsl:value-of select="."/>
            </clariah:value>
        </xsl:copy>
    </xsl:template>    
        
    <!-- HANDLE cue attributes -->
    
    <!-- cues in the profile -->
    
    <xsl:template match="text()" mode="cue"/>
    
    <xsl:template match="@cue:*" mode="cue">
        <xsl:param name="tweak"/>
        <xsl:variable name="cue" select="local-name()"/>
        <xsl:variable name="myTweak" select="$tweak/@cue:*[local-name()=$cue]"/>
        <xsl:message>DBG: cue[<xsl:value-of select="$cue"/>] tweak[<xsl:value-of select="$myTweak"/>]</xsl:message>
        <xsl:choose>
            <xsl:when test="$myTweak">
                <!-- keep the tweak cue -->
                <xsl:message>DBG: tweak cue[<xsl:value-of select="$cue"/>]=[<xsl:value-of select="$myTweak"/>]</xsl:message>
                <xsl:apply-templates select="$myTweak" mode="copy"/>
            </xsl:when>
            <xsl:otherwise>
                <!-- keep the profile cue -->
                <xsl:message>DBG: profile cue[<xsl:value-of select="$cue"/>]=[<xsl:value-of select="."/>]</xsl:message>
                <xsl:copy/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <!-- cues in the tweak -->
    
    <xsl:template match="text()" mode="tweak-cue"/>
    
    <xsl:template match="@cue:*" mode="tweak-cue">
        <xsl:param name="prof"/>
        <xsl:variable name="cue" select="local-name()"/>
        <xsl:variable name="myProf" select="$prof/@cue:*[local-name()=$cue]"/>
        <xsl:message>DBG: cue[<xsl:value-of select="$cue"/>] prof[<xsl:value-of select="$myProf"/>]</xsl:message>
        <xsl:choose>
            <xsl:when test="$myProf">
                <!-- will have been overwritten in the cue run -->
                <xsl:message>DBG: cue[<xsl:value-of select="$cue"/>] already overwritten</xsl:message>
            </xsl:when>
            <xsl:otherwise>
                <!-- keep the tweak cue -->
                <xsl:message>DBG: tweak cue[<xsl:value-of select="$cue"/>]=[<xsl:value-of select="."/>]</xsl:message>
                <xsl:copy/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <!-- HANDLE CMD attributes -->
    
    <xsl:template match="text()" mode="cmd"/>
    
    <xsl:template match="@*" mode="cmd">
        <xsl:copy/>
    </xsl:template>
        
    <!-- skip cues -->
    
    <xsl:template match="cue:*" mode="cmd"/>
    
    <!-- value schemes -->
    
    <xsl:template match="@ValueScheme">
        <xsl:param name="tweak"/>
        <xsl:variable name="myTweak" select="$tweak/@ValueScheme"/>
        <xsl:choose>
            <xsl:when test=".!='string' and normalize-space($myTweak)!='' and $myTweak!='string'">
                <xsl:message terminate="yes">ERR: only a string value scheme can be overwritten!</xsl:message>
            </xsl:when>
            <xsl:when test=".='string' and normalize-space($myTweak)!=''">
                <xsl:if test="$tweak/ValueScheme">
                    <xsl:message>WRN: the tweaked ValueScheme element is ignored as the @ValueScheme is overwritten!</xsl:message>
                </xsl:if>
                <xsl:apply-templates select="$myTweak" mode="copy"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:copy/>
            </xsl:otherwise>
        </xsl:choose>
        
    </xsl:template>
    
    <!-- attribute required -->
    
    <xsl:template match="@Required">
        <xsl:param name="tweak"/>
        <xsl:variable name="myTweak" select="$tweak/@Required"/>
        <xsl:choose>
            <xsl:when test=".='true' and $myTweak='false'">
                <xsl:message terminate="yes">ERR: a mandatory attribute[<xsl:value-of select="parent::Attribute/@name"/>] cannot become optional!</xsl:message>
            </xsl:when>
            <xsl:when test=".='false' and $myTweak='true'">
                <xsl:apply-templates select="$myTweak" mode="copy"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:copy/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <!-- tweak cardinalities -->

    <xsl:template match="@CardinalityMin" mode="cmd">
        <xsl:param name="tweak"/>
        <xsl:variable name="myTweak" select="$tweak/@CardinalityMin"/>
        <xsl:choose>
            <xsl:when test="normalize-space($myTweak)!=''">
                <xsl:variable name="min" select="."/>
                <xsl:variable name="max">
                    <xsl:choose>
                        <xsl:when test="normalize-space(../@CardinalityMax)='unbounded'">
                            <xsl:value-of select="number($myTweak) + 1"/>
                        </xsl:when>
                        <xsl:when test="normalize-space(../@CardinalityMax)!=''">
                            <xsl:value-of select="../@CardinalityMax"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="'1'"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>
                <xsl:choose>
                    <xsl:when test="(number($min) &lt;= number($myTweak)) and (number($myTweak) &lt;= number($max))">
                        <xsl:attribute name="CardinalityMin">
                            <xsl:value-of select="number($myTweak)"/>
                        </xsl:attribute>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:message terminate="yes">ERR: the minimum cardinality tweak is invalid! (<xsl:value-of select="$myTweak"/> is not in the range [<xsl:value-of select="$min"/>-<xsl:value-of select="$max"/>])</xsl:message>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:otherwise>
                <xsl:attribute name="CardinalityMin">
                    <xsl:value-of select="."/>
                </xsl:attribute>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <xsl:template match="@CardinalityMax" mode="cmd">
        <xsl:param name="tweak"/>
        <xsl:variable name="myTweak" select="$tweak/@CardinalityMax"/>
        <xsl:choose>
            <xsl:when test="normalize-space($myTweak)!=''">
                <xsl:variable name="max" select="."/>
                <xsl:variable name="min">
                    <xsl:choose>
                        <xsl:when test="normalize-space(../@CardinalityMin)='unbounded'">
                            <xsl:message>WRN: an unbounded minimum cardinality is nonsense!</xsl:message>
                            <xsl:value-of select="'0'"/>
                        </xsl:when>
                        <xsl:when test="normalize-space(../@CardinalityMin)!=''">
                            <xsl:value-of select="../@CardinalityMin"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="'1'"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>
                <xsl:choose>
                    <xsl:when test="(number($min) &lt;= number($myTweak)) and (number($myTweak) &lt;= number($max))">
                        <xsl:attribute name="CardinalityMax">
                            <xsl:value-of select="number($myTweak)"/>
                        </xsl:attribute>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:message terminate="yes">ERR: the maximum cardinality tweak is invalid! (<xsl:value-of select="$myTweak"/> is not in the range [<xsl:value-of select="$min"/>-<xsl:value-of select="$max"/>])</xsl:message>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:otherwise>
                <xsl:attribute name="CardinalityMax">
                    <xsl:value-of select="."/>
                </xsl:attribute>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- identity copy -->
    <xsl:template match="node() | @*" mode="copy">
        <xsl:copy>
            <xsl:apply-templates select="node() | @*" mode="copy"/>
        </xsl:copy>
    </xsl:template>
    
    <xsl:template match="text()" mode="copy" priority="1">
        <xsl:if test="normalize-space(.)!=''">
            <xsl:copy/>
        </xsl:if>
    </xsl:template>    
    
</xsl:stylesheet>