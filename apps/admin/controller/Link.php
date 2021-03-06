<?php
// 链接控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\Links;
use app\admin\builder\Builder;

class Link extends Admin{
    
    protected $linkModel;
    protected $linkType;

    function _initialize()
    {
        parent::_initialize();
        $this->linkModel = new Links();
        $this->linkType  = [
                    1 => '友情链接',
                    2 => '合作伙伴'
                ];
    }

    public function index() {
        // 搜索
        $keyword =$this->request->param('keyword');
        if ($keyword) {
            $this->linkModel->where('id|title','like','%'.$keyword.'%');
        }

        // 获取所有链接
        $map['status'] = ['egt', '0'];  // 禁用和正常状态
        list($data_list,$page) = $this->linkModel->getListByPage($map,'sort,create_time desc','*',15);

        Builder::run('List')
                ->setMetaTitle('友情链接')  // 设置页面标题
                ->addTopButton('addnew')    // 添加新增按钮
                ->addTopButton('resume')  // 添加启用按钮
                ->addTopButton('forbid')  // 添加禁用按钮
                ->addTopButton('sort')  // 添加排序按钮
                ->setSearch('请输入关键字', url('index'))
                ->keyListItem('title', '站点名称')
                ->keyListItem('url', '链接地址','url',array('target'=>'_blank'))
                ->keyListItem('image', '图像', 'picture')
                ->keyListItem('type', '类型', 'array',$this->linkType)
                ->keyListItem('rating','评级')
                ->keyListItem('sort', '排序')
                ->keyListItem('status', '状态', 'status')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListData($data_list)     // 数据列表
                ->setListPage($page)  // 数据列表分页
                ->addRightButton('edit')           // 添加编辑按钮
                ->addRightButton('forbid')  // 添加禁用/启用按钮
                ->addRightButton('delete',['model'=>'Links'])  // 添加删除按钮
                ->fetch();
    }

    /**
     * 编辑链接
     */
    public function edit($id=0) {
        $title = $id>0 ? "编辑":"新增";
        if (IS_POST) {
            $data = input('param.');
            //验证数据
            $this->validateData($data,'admin/Link');

            $id   =isset($data['id']) && $data['id']>0 ? $data['id']:false;
            if ($this->linkModel->editData($data,$id)) {
                $this->success($title.'成功', url('index'));
            } else {
                $this->error($this->linkModel->getError());
            }

        } else {
            $info = ['type'=>1,'target'=>'_blank','rating'=>0,'sort'=>99];
            if ($id>0) {
                $info = Links::get($id);
            }

            Builder::run('Form')
                    ->setMetaTitle($title.'链接')  // 设置页面标题
                    ->addFormItem('id', 'hidden', 'ID', 'ID')
                    ->addFormItem('title', 'text', '站点名称', '请输入链接站点名称')
                    ->addFormItem('url', 'text', '链接地址', '请填写带http://的全路径')
                    ->addFormItem('image', 'picture', '图像', '可以是网站Logo')
                    ->addFormItem('target', 'select', '打开方式', '',['_blank'=>'新的窗口打开','_self'=>'本窗口打开'])
                    ->addFormItem('type', 'radio', '类型', '',$this->linkType)
                    ->addFormItem('rating', 'number', '级别', '用于评级别')
                    ->addFormItem('sort', 'number', '排序', '用于显示的顺序')
                    ->setFormData($info)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
    }
    
    /**
     * 对链接进行排序
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function sort($ids = null)
    {
        $builder = Builder::run('Sort');
        if (IS_POST) {
            $builder->doSort('links', $ids);
        } else {
            $map['status'] = ['egt', 0];
            $list = $this->linkModel->selectByMap($map, 'sort asc', 'id,title,sort');
            foreach ($list as $key => $val) {
                $list[$key]['title'] = $val['title'];
            }
            $builder->setMetaTitle('配置排序')
                    ->setListData($list)
                    ->addButton('submit')->addButton('back')
                    ->fetch();
        }
    }

}