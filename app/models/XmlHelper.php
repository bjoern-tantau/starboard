<?php

class XmlHelper
{

    /**
     * Recursivly merge nodes of the same name to one node.
     * @param SimpleXMLElement $xml
     *
     * @return SimpleXMLElement
     */
    public static function merge(SimpleXMLElement $xml)
    {
        if (func_num_args() > 2) {
            $args = func_get_args();
            array_shift($args);
            $xml2 = call_user_func_array(array(get_class(), 'merge'), $args);
        } else if (func_num_args() == 2) {
            $args = func_get_args();
            $xml2 = $args[1];
        } else {
            $xml2 = null;
        }

        if ($xml2 instanceof SimpleXMLElement) {
            $xmlName = $xml->getName();
            $xml2Name = $xml2->getName();
            if (!$xml->count() && !$xml2->count()) {
                $xml->{0} = (string) $xml2;
                self::mergeAttributes($xml, $xml2);
            } else if ($xml->getName() == $xml2->getName()) {
                self::mergeAttributes($xml, $xml2);
                foreach ($xml2->children() as $child) {
                    self::appendChild($xml, clone $child);
                }
            } else {
                $parent = $xml->xpath('..');
                self::appendChild($parent, $xml2);
            }
        }
        if ($xml->count()) {
            $children = array();
            $toDeletes = array();
            foreach ($xml->children() as $child) {
                if (isset($children[$child->getName()])) {
                    $children[$child->getName()] = self::merge($children[$child->getName()], $child);
                    $toDeletes[] = $child;
                } else {
                    $children[$child->getName()] = $child;
                }
            }
            foreach ($toDeletes as $toDelete) {
                self::deleteNode($toDelete);
            }
        }
        return $xml;
    }

    /**
     * Merge the attributes of the given Elements.
     *
     * @param SimpleXMLElement $xml
     * @param SimpleXMLElement $xml2
     * @return SimpleXMLElement
     */
    protected static function mergeAttributes(SimpleXMLElement $xml, SimpleXMLElement $xml2)
    {
        foreach ($xml2->attributes() as $attribute) {
            if (isset($xml[$attribute->getName()])) {
                $xml[$attribute->getName()] = $attribute;
            } else {
                $xml->addAttribute($attribute->getName(), $attribute);
            }
        }
        return $xml;
    }

    /**
     * Delete given node.
     *
     * @param SimpleXMLElement $node
     *
     * @return void
     */
    public static function deleteNode(SimpleXMLElement $node)
    {
        $dom = dom_import_simplexml($node);
        $dom->parentNode->removeChild($dom);
    }

    /**
     * Append a child node.
     *
     * @param SimpleXMLElement $parent
     * @param SimpleXMLElement $child
     *
     * @return SimpleXMLElement
     */
    public static function appendChild(SimpleXMLElement $parent, SimpleXMLElement $child)
    {
        $parentDom = dom_import_simplexml($parent);
        $childDom = $parentDom->ownerDocument->importNode(dom_import_simplexml($child), true);
        $parentDom->appendChild($childDom);
        return $parent;
    }

}
