<?php

//Admin Routs
OW::getRouter()->addRoute(new OW_Route('articles.admin_index', 'admin/articles', 'ARTICLES_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('articles.admin-view', 'admin/articles/:id', 'ARTICLES_CTRL_Admin', 'view'));
OW::getRouter()->addRoute(new OW_Route('articles.admin-delete', 'admin/articles/delete', 'ARTICLES_CTRL_Admin', 'delete'));
OW::getRouter()->addRoute(new OW_Route('articles.admin-edit', 'admin/articles/edit/:id', 'ARTICLES_CTRL_Admin', 'edit'));

OW::getRouter()->addRoute(new OW_Route('articles-viewlist', 'articles', 'ARTICLES_CTRL_Articles', 'viewlist'));
OW::getRouter()->addRoute(new OW_Route('articles-view', 'articles/:id', 'ARTICLES_CTRL_Articles', 'view'));
OW::getRouter()->addRoute(new OW_Route('articles-update-featured', 'articles/update_featured', 'ARTICLES_CTRL_Articles', 'updateFeatured'));
