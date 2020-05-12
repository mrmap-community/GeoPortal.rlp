<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:variable name="sortBy" select="/result/@sortBy" />
    <xsl:variable name="searchResources" select="/result/@searchResources" />
    <xsl:variable name="mapBenderURL" select="/result/@mapBenderURL" />
    <xsl:variable name="pages" select="/result/@pagesCount" />
    <xsl:variable name="page" select="/result/@page" />
    <xsl:variable name="nextPage" select="/result/@pageURL" />
    <xsl:variable name="pageItemCount" select="/result/@pageItemCount" />
    <xsl:output method="html"/>
    <xsl:template match="/">
        <!--html>
            <head>
                <meta http-equiv="content-Type" content="text/html; charset=UTF-8" />
            </head>
            <body-->
        <div id="content_xsl">
            <div style="text-align:center;margin-bottom:20px;">
                <xsl:apply-templates select="./result" mode="pages">
                    <xsl:with-param name="num" select="1" />
                </xsl:apply-templates>
            </div>
            <xsl:apply-templates select="./result" mode="content"/>
        </div>
        <!--/body>
        </html-->
    </xsl:template>
    <xsl:template match="result" mode="pages">
        <xsl:param name="num" />
        <xsl:choose>
            <xsl:when test="$num=$page">
                <span style="color:#FFFFFF;background-color:#4F7AA5;padding:8px;font-weight:bold;margin:3px;"><xsl:value-of select="$num" /></span>
            </xsl:when>
            <xsl:otherwise>
                <span style="color:#5A5B5F;background-color:#CCCCCC;padding:8px;font-weight:bold;margin:3px;cursor:pointer;" onclick="javascript:jQuery('#incontent').load(String('{$nextPage}&amp;page={$num} #content_xsl').replace(/\&amp;/g, String.fromCharCode(38)))"><xsl:value-of select="$num" /></span>
            </xsl:otherwise>
        </xsl:choose>
        <!--xsl:variable name="test" select="sum($num,$pages)" /-->
        <xsl:if test="$pages>$num">
            <xsl:apply-templates select="." mode="pages">
                <xsl:with-param name="num" select="$num + 1" />
            </xsl:apply-templates>
        </xsl:if>
    </xsl:template>
    <xsl:template match="result" mode="content">
        <div style="maprgin-top:20px;maprgin-bottom:40px;"><span style="font-weight:bold;color:#4F7AA5;">Darstellungsdienste</span><br/><span style="font-weight:bold;">(<xsl:value-of select="./@totalCount"/> Treffer in <xsl:value-of select="./@totalTime"/> Sekunden)</span></div>
        <div style="maprgin-top:20px;">
            <table width="100%" style="border-width: 0px;" cellpadding="0" cellspacing="0">
                <tr>
                    <th width="10%" style="color:#5A5B5F;font-weight:bold;background-color:#CCCCCC;padding: 5px 5px;">Dienste:</th>
                    <th width="80%" style="background-color:#CCCCCC;"></th>
                    <th style="background-color:#CCCCCC;" width="10%"></th>
                </tr>
                <xsl:for-each select="./child::*/@error">
                    <tr><td colspan="3" style="color:red;padding: 5px 5px;">Error: <xsl:value-of select="."/></td></tr>
                </xsl:for-each>
                <xsl:for-each select="./child::*/wmsresult">
                    <xsl:sort select="./@title" data-type="text" case-order="upper-first"/>
                    <xsl:variable name="minpos" select="(($page)-(number(1)))*$pageItemCount+number(1)" />
                    <xsl:variable name="maxpos" select="($minpos)+($pageItemCount)" />
                    <xsl:if test="position()>=$minpos and $maxpos>position()">
                        <xsl:choose>
                            <xsl:when test="./@url != ''">
                                <tr>
                                    <td style="vertical-align:middle;padding-left:8px;" >
                                        <img src="fileadmin/design/Mapset.png" />
                                        <img alt="Layer" src="fileadmin/design/icn_layer.png" />
                                    </td>
                                    <td style="">
                                        <span style="font-weight:bold;color:#4F7AA5;"><xsl:value-of select="./@title"/></span><br/>
                                        <span><xsl:value-of select="./@abstract"/></span> <span>Quelle: <xsl:value-of select="./@source"/></span>
                                    </td>
                                    <td  style="vertical-align:middle;text-align:right;padding-right:8px;">
                                        <nobr><a href="{$mapBenderURL}&amp;WMS={./@url}" > <img src="../frames/fileadmin/design/icn_zoommap.png" border="0" title="{.}" /></a> <a href="{$mapBenderURL}&amp;WMS={./@url}" ><img src="../frames/fileadmin/design/icn_map.png" border="0" title="{.}" /></a></nobr>
                                    </td>
                                </tr>
                            </xsl:when>
                            <xsl:when test="./@layerId != ''">
                                <tr>
                                    <td style="vertical-align:middle;padding-left:8px;">
                                        <nobr>
                                            <img src="fileadmin/design/Mapset.png" />
                                            <img alt="Layer" src="fileadmin/design/icn_layer.png" />
                                        </nobr>
                                    </td>
                                    <td style="">
                                        <span style="font-weight:bold;color:#4F7AA5;"><xsl:value-of select="./@title"/></span><br/>
                                        <span><xsl:value-of select="./@abstract"/></span> <span>Quelle: <xsl:value-of select="./@source"/></span>
                                    </td>
                                    <td style="vertical-align:middle;text-align:right;padding-right:8px;">
                                        <nobr>
                                            <a href="{$mapBenderURL}&amp;LAYER[id]={./@layerId}" > <img src="../frames/fileadmin/design/icn_zoommap.png" border="0" title="{.}" /></a>
                                            <a href="{$mapBenderURL}&amp;LAYER[id]={./@layerId}" > <img src="../frames/fileadmin/design/icn_map.png" border="0" title="{.}" /></a>
                                        </nobr>
                                    </td>
                                </tr>
                            </xsl:when>
                            <xsl:otherwise></xsl:otherwise>
                        </xsl:choose>
                    </xsl:if>
                </xsl:for-each>
            </table>
        </div>
    </xsl:template>
</xsl:stylesheet>
