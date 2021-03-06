<?php

/**
 * Holds a keyword obtained from the API
 */
class KeywordApi extends CRUDApiClient {

  protected $groupName;

  protected $keyword;

  public static function getListWithCriteria(array $properties, $printErrorMessage = TRUE) {
    return parent::getListWithCriteriaForClass(__CLASS__, $properties, $printErrorMessage);
  }

  /**
   * Return the group name
   *
   * @return string The group name
   */
  public function getGroupName() {
    return $this->groupName;
  }

  /**
   * Return the keyword
   *
   * @return string The keyword
   */
  public function getKeyword() {
    return $this->keyword;
  }

  /**
   * Get the available keyword groups.
   *
   * @return array An array of group names.
   */
  public static function getGroups() {
    $groups = [];
    foreach (CachedConferenceApi::getKeywords() as $keyword) {
      $groups[] = $keyword->getGroupName();
    }
    return array_unique($groups);
  }

  /**
   * Returns the keyword name of the current group
   *
   * @param string $group The name of the group of keywords
   * @param bool $singular Whether the singular or plural form should be
   *   returned
   * @param bool $lowercase Whether it should be all lowercase
   *
   * @return string The keyword name
   */
  public static function getKeywordName($group, $singular = TRUE, $lowercase = FALSE) {
    if ($singular) {
      $keywordSingularMap = SettingsApi::getMapOfValues(SettingsApi::getSetting(SettingsApi::KEYWORD_NAME_SINGULAR));
      $keywordName = isset($keywordSingularMap[$group])
        ? $keywordSingularMap[$group] : 'Keyword';
    }
    else {
      $keywordPluralMap = SettingsApi::getMapOfValues(SettingsApi::getSetting(SettingsApi::KEYWORD_NAME_PLURAL));
      $keywordName = isset($keywordPluralMap[$group])
        ? $keywordPluralMap[$group] : 'Keywords';
    }

    if ($lowercase) {
      $keywordName = strtolower($keywordName);
    }

    return $keywordName;
  }

  public function __toString() {
    return $this->getKeyword();
  }
} 
