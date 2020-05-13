<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:variable name="generateTime" select="/result/@generateTime" />
    <xsl:variable name="totalHits" select="/result/@totalHits" />
    <xsl:variable name="mapBenderURL" select="/result/@mapBenderURL" />
    <xsl:variable name="pageNumber" select="/result/@pageNumber" />
    <xsl:variable name="itemsPerPage" select="/result/@itemsPerPage" />
    <xsl:output method="html"/>
    <xsl:template match="/">
        {"wms":{"md":{"nresults":"<xsl:value-of select="$totalHits"/>",
        "p":"<xsl:value-of select="$pageNumber"/>",
        "rpp":<xsl:value-of select="$itemsPerPage"/>,
        "genTime":<xsl:value-of select="$generateTime"/>
        },"srv":[
        <xsl:apply-templates select="./result" mode="content"/>
        ]}}
    </xsl:template>
    <xsl:template match="result" mode="content">
        <xsl:for-each select="./child::*/wmsresult">
            <xsl:sort select="./@titleToSort" data-type="text" case-order="upper-first"/>
            <xsl:variable name="minpos" select="(($pageNumber)-(number(1)))*$itemsPerPage+number(1)" />
            <xsl:variable name="maxpos" select="($minpos)+($itemsPerPage)" />
            <xsl:if test="position()>=$minpos and $maxpos>position()">
                <xsl:choose>
                    <xsl:when test="./@url != ''">
                        <xsl:if test="position()!=$minpos">,</xsl:if>
                        {"title":"<xsl:value-of select="translate(./@title,'&quot;',&quot;'&quot;)"/>",
                         "abstr":"<xsl:value-of select="./@abstract"/>",
                         "source":"<xsl:value-of select="./@source"/>",
                         "mburl":"<xsl:value-of select="$mapBenderURL"/>&amp;WMS=<xsl:value-of select="./@url"/>"}
                    </xsl:when>
                    <xsl:when test="./@layerId != ''">
                        <xsl:if test="position()!=$minpos">,</xsl:if>
                        {"title":"<xsl:value-of select="./@title"/>",
                         "abstr":"<xsl:value-of select="./@abstract"/>",
                         "source":"<xsl:value-of select="./@source"/>",
                         "mburl":"<xsl:value-of select="$mapBenderURL"/>&amp;LAYER[id]=<xsl:value-of select="./@layerId"/>"}
                    </xsl:when>
                    <xsl:otherwise />
                </xsl:choose>
            </xsl:if>
        </xsl:for-each>
    </xsl:template>
</xsl:stylesheet>
