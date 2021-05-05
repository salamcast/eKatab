<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:h="http://www.w3.org/1999/xhtml"
  xmlns:s="http://www.w3.org/2000/svg"
  xmlns:xlink="http://www.w3.org/1999/xlink"
  xmlns:ibooks="http://www.apple.com/2011/iBooks" 
  xmlns:m="http://www.w3.org/1998/Math/MathML" 
  xmlns:epub="http://www.idpf.org/2007/ops"
  version="1.0">
  <xsl:output method="html" media-type="text/html"/>
  <xsl:template match="/"><xsl:apply-templates /></xsl:template>
  <xsl:template match="html[1]|h:html[1]|body[1]|h:body[1]"><xsl:apply-templates  /></xsl:template> 
  <xsl:template match="head|h:head|h:title|title|link|h:link|style|h:style|h:meta|meta"></xsl:template>
  
  <xsl:template match="a|h:a">
    <xsl:if test=".!=''">
      <a  >
        <xsl:attribute name="href"><xsl:value-of select="@href"/></xsl:attribute>
        <xsl:attribute name="data-ajax">false</xsl:attribute>
        <xsl:attribute name="data-transition">flip</xsl:attribute>
        <xsl:apply-templates />
      </a>
    </xsl:if>
  </xsl:template>
  <xsl:template match="img|h:img">
    <xsl:element name="img">
      <xsl:if test="@id!=''"><xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute></xsl:if>
      <xsl:attribute name="src" ><xsl:value-of select="@src"/></xsl:attribute>
    </xsl:element>
  </xsl:template>
  <xsl:template match="s:svg|svg">
    <xsl:if test="s:image/@xlink:href|image/@href">
      <xsl:element name="img">
        <xsl:attribute name="src">
          <xsl:choose>
            <xsl:when test="starts-with(s:image/@xlink:href|image/@href, '../')">
              <xsl:value-of select="substring(s:image/@xlink:href|image/@href, 4)"/>
            </xsl:when>
            <xsl:otherwise><xsl:value-of select="s:image/@xlink:href|image/@href"/></xsl:otherwise>
          </xsl:choose>
        </xsl:attribute>
        <xsl:attribute name="width"><xsl:value-of select="s:image/@width|image/@width"/></xsl:attribute>
        <xsl:attribute name="height"><xsl:value-of select="s:image/@height|image/@height"/></xsl:attribute>     
      </xsl:element>      
    </xsl:if>
  </xsl:template>
  
  <xsl:template match="*">
    <xsl:choose>
      <xsl:when test=".='&#160;'"></xsl:when>
      <xsl:when test=".=''"><xsl:apply-templates /></xsl:when>
      <xsl:otherwise>
        <xsl:element name="{name()}">
          <xsl:for-each select="@*">
            <xsl:choose>
              <xsl:when test="name()='style'"></xsl:when>
              <xsl:otherwise>
                <xsl:attribute name="{name()}"><xsl:value-of select="."/></xsl:attribute>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:for-each>
          <xsl:apply-templates />
        </xsl:element>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>