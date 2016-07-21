<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns="http://www.idpf.org/2007/opf" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
  xmlns:opf="http://www.idpf.org/2007/opf" 
  xmlns:dcterms="http://purl.org/dc/terms/" 
  xmlns:calibre="http://calibre.kovidgoyal.net/2009/metadata" 
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  version="1.0">
  <xsl:output  method="text"/>
  <!-- 
    the prefix should be a dir prefix to the file from the root of the zip
  - like OEBPS or OPS or what ever the dir the content.opf file is located in
  -->
  <xsl:param name="prefix" />
  <xsl:param name="base" >http://localhost/index.php</xsl:param>
  <xsl:param name="zip">zip://</xsl:param>
  <xsl:template match="/">[book_info]
   <xsl:choose>
    <xsl:when test="/package/guide/reference/@href|/opf:package/opf:guide/opf:reference/@href">
first="<xsl:value-of select="$zip"/><xsl:value-of select="/package/guide/reference/@href|/opf:package/opf:guide/opf:reference/@href"/>"
     </xsl:when>
     <xsl:otherwise><!-- /package/manifest/item[@media-type='application/xhtml+xml']/@href|/opf:package/opf:manifest/opf:item[@media-type='application/xhtml+xml']/@href -->
first="<xsl:value-of select="$zip"/><xsl:value-of select="/package/manifest/item[@media-type='application/xhtml+xml']/@href|/opf:package/opf:manifest/opf:item[@media-type='application/xhtml+xml']/@href"/>"
     </xsl:otherwise>
   </xsl:choose>    
title="<xsl:value-of select="/package/metadata/dc:title|/opf:package/opf:metadata/dc:title"/>"
creator="<xsl:value-of select="/package/metadata/dc:creator|/opf:package/opf:metadata/dc:creator"/>"
publisher="<xsl:value-of select="/package/metadata/dc:publisher|/opf:package/opf:metadata/dc:publisher"/>"
description="<xsl:value-of select="/package/metadata/dc:description|/opf:package/opf:metadata/dc:description"/>"
subject="<xsl:value-of select="/package/metadata/dc:subject|/opf:package/opf:metadata/dc:subject"/>"
date="<xsl:value-of select="/package/metadata/dc:date|/opf:package/opf:metadata/dc:date"/>"
language="<xsl:value-of select="/package/metadata/dc:language|/opf:package/opf:metadata/dc:language"/>"
toc="<xsl:value-of select="$zip"/><xsl:apply-templates select="//spine|//opf:spine" mode="toc" />"
[book_layout]<xsl:apply-templates select="//spine|//opf:spine" />
[manifest]<xsl:apply-templates select="//manifest/item|//opf:manifest/opf:item" mode="manifest"/>
  </xsl:template>
  
  <xsl:template match="//spine|//opf:spine" mode='toc'><xsl:call-template name="get_toc" /></xsl:template>
  
  <xsl:template name="get_toc"><xsl:param name="id" select="@toc" /><xsl:value-of select="//manifest/item[@id=$id]/@href|//opf:manifest/opf:item[@id=$id]/@href"/></xsl:template>
  <!-- Generate a book order based on the spine tag -->
  <xsl:template match="//spine|//opf:spine"><xsl:call-template name="item_ini"><xsl:with-param name="id" select="@toc" /></xsl:call-template>
    <xsl:apply-templates select="itemref|opf:itemref" />
  </xsl:template>
  
  <xsl:template match="//manifest/item|//opf:manifest/opf:item" mode="manifest">
    <xsl:call-template name="item_ini"><xsl:with-param name="id" select="@id" /></xsl:call-template>
  </xsl:template>
  
  <!-- make a list of links in proper book order -->
  <xsl:template match="itemref|opf:itemref"><xsl:call-template name="item_ini"><xsl:with-param name="id" select="@idref" />
    <xsl:with-param name="prev" select="position()-1" />
    <xsl:with-param name="next" select="position()+1" /></xsl:call-template></xsl:template>
  <!-- Add new array items -->
  <xsl:template name="item_ini"><xsl:param name="id" /><xsl:param name='prev' /><xsl:param name='next' />
    <xsl:param name="n"><xsl:value-of select="//itemref[$next]/@idref|//opf:itemref[$next]/@idref"/></xsl:param><xsl:param name="p"><xsl:value-of select="//itemref[$prev]/@idref|//opf:itemref[$prev]/@idref"/></xsl:param>
; @id => <xsl:value-of select="$id"/>
id[]="<xsl:value-of select="$id"/>"
type[]="<xsl:value-of select="//item[@id=$id]/@media-type|//opf:item[@id=$id]/@media-type"/>"
url[]="<xsl:value-of select="$prefix"/><xsl:value-of select="//item[@id=$id]/@href|//opf:item[@id=$id]/@href"/>"
zip[]="<xsl:value-of select="$zip"/><xsl:value-of select="//item[@id=$id]/@href|//opf:item[@id=$id]/@href"/>"
href[]="<xsl:value-of select="//item[@id=$id]/@href|//opf:item[@id=$id]/@href"/>"
prev[]="<xsl:if test="$p!=''" ><xsl:value-of select="$base"/>/<xsl:value-of select="//item[@id=$p]/@href|//opf:item[@id=$p]/@href"/></xsl:if>"
rest[]="<xsl:value-of select="$base"/>/<xsl:value-of select="//item[@id=$id]/@href|//opf:item[@id=$id]/@href"/>"
next[]="<xsl:if test="$n!=''" ><xsl:value-of select="$base"/>/<xsl:value-of select="//item[@id=$n]/@href|//opf:item[@id=$n]/@href"/></xsl:if>"
  </xsl:template>
</xsl:stylesheet>