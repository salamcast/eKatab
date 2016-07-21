<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:c="urn:oasis:names:tc:opendocument:xmlns:container" 
  xmlns:d="http://www.w3.org/2000/09/xmldsig#" 
  xmlns:e="http://www.w3.org/2001/04/xmlenc#"
  version="1.0">
<xsl:output method="text"/>
 <xsl:template match="/">[encryption]
<xsl:apply-templates select="//e:EncryptedData|//EncryptedData"/>
 </xsl:template>
  <xsl:template match="//e:EncryptedData|//EncryptedData">;<xsl:value-of select="//e:CipherReference/@URI|//CipherReference/@URI"/>
url[]="<xsl:value-of select="//e:CipherReference/@URI|//CipherReference/@URI"/>"
method[]="<xsl:value-of select="e:EncryptionMethod/@Algorithm|EncryptionMethod/@Algorithm"/>"
key_val[]="<xsl:value-of select="//d:KeyName|//KeyName"/>"
</xsl:template>
</xsl:stylesheet>