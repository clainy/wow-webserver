<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class ItemsetList extends BaseType
{
    use ListviewHelper;

    private   $classes    = [];                             // used to build g_classes
    public    $pieceToSet = [];                             // used to build g_items and search

    protected $setupQuery = 'SELECT *, id AS ARRAY_KEY FROM ?_itemset WHERE [filter] [cond] ORDER BY maxlevel DESC';
    protected $matchQuery = 'SELECT COUNT(1) FROM ?_itemset WHERE [filter] [cond]';

    public function __construct($data, $applyFilter = false)
    {
        parent::__construct($data, $applyFilter);

        // post processing
        while ($this->iterate())
        {
            $this->templates[$this->id]['classes'] = [];
            $this->templates[$this->id]['pieces']  = [];
            for ($i = 1; $i < 12; $i++)
            {
                if ($this->curTpl['classMask'] & (1 << ($i - 1)))
                {
                    $this->classes[] = $i;
                    $this->templates[$this->id]['classes'][] = $i;
                }
            }

            for ($i = 1; $i < 10; $i++)
            {
                if ($piece = $this->curTpl['item'.$i])
                {
                    $this->templates[$this->id]['pieces'][] = $piece;
                    $this->pieceToSet[$piece] = $this->id;
                }
            }
        }
        $this->reset();

        $this->classes = array_unique($this->classes);
    }

    public function getListviewData()
    {
        $data = [];

        while ($this->iterate())
        {
            $data[$this->id] = array(
                'id'       => $this->id,
                'idbak'    => $this->curTpl['refSetId'],
                'name'     => $this->getField('name', true),
                'quality'  => 7 - $this->curTpl['quality'],
                'minlevel' => $this->curTpl['minLevel'],
                'maxlevel' => $this->curTpl['maxLevel'],
                'note'     => $this->curTpl['contentGroup'],
                'type'     => $this->curTpl['type'],
                'heroic'   => $this->curTpl['heroic'] == 1, // we want to be bool
                'reqclass' => $this->curTpl['classMask'],
                'classes'  => $this->curTpl['classes'],
                'pieces'   => $this->curTpl['pieces'],
                'heroic'   => $this->curTpl['heroic']
            );
        }

        return $data;
    }

    public function addGlobalsToJscript(&$refs)
    {
        if ($this->classes)
            (new CharClassList(array(['id', $this->classes])))->addGlobalsToJscript($refs);

        if ($this->pieceToSet)
            (new ItemList(array(['i.entry', array_keys($this->pieceToSet)], 0)))->addGlobalsToJscript($refs);
    }

    public function addRewardsToJScript(&$ref) { }
    public function renderTooltip() { }
}

?>