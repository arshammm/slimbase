<?php
/*****************************
 * App hooks for database upgrades

$app->hook('slim.before', function() use ($app,$session) {
  //make sure that the App has been installed fully
  if( file_exists('../app/includes/config.php') ){
    //check if a updates table exists
    try{
      $updates = $app->db->query('SELECT COUNT(*) as table_columns FROM information_schema.tables WHERE table_schema = "'.DBNAME.'" AND table_name = "db_updates"');
      $result = $updates->fetch();
      if( (int) $result->table_columns === 0 ){
        $app->db->exec('CREATE TABLE IF NOT EXISTS db_updates (
          update_hash VARCHAR(32),
          update_date DATETIME,
          PRIMARY KEY (update_hash))
          ENGINE = InnoDB 
          DEFAULT CHARACTER SET = utf8 
          COLLATE = utf8_unicode_ci 
          COMMENT = "store database updates to the site";');

        $update_0 = $app->db->exec('INSERT INTO db_updates (update_hash, update_date) VALUES ("9719e91314a75b6dc68fefd0e6042d51", NOW())');
      }else{
        //check the hash of updates
        $updates = $app->db->query('SELECT update_hash FROM db_updates');
        $hashes = $updates->fetchAll(PDO::FETCH_ASSOC);

        if( !in_array_r('5faMDDtgCDKwWtx2dp9scvsLNMrJ9MDk',$hashes) ){
          //check if a status table exists
          try{
            $status_table = $app->db->query('SELECT COUNT(*) as table_columns FROM information_schema.tables WHERE table_schema = "'.$app->db_name.'" AND table_name = "status_messages"');
            $result = $status_table->fetch();
            if( (int) $result->table_columns === 0 ){
              try{
                $create_status_table = $app->db->exec('CREATE TABLE IF NOT EXISTS status_messages (
                  status_id INT NOT NULL AUTO_INCREMENT,
                  short_id INT,
                  message TEXT,
                  social_site TINYINT,
                  user_id INT(11),
                  PRIMARY KEY (status_id))
                  ENGINE = InnoDB 
                  DEFAULT CHARACTER SET = utf8 
                  COLLATE = utf8_unicode_ci 
                  COMMENT = "social status messages user posted";');

                $add_foreign_key = $app->db->exec('ALTER TABLE `status_messages` ADD CONSTRAINT `fk1_status_messages` FOREIGN KEY (`short_id`) REFERENCES short_urls(`short_id`) ON DELETE CASCADE ON UPDATE CASCADE');

                $add_foreign_key = $app->db->exec('ALTER TABLE `status_messages` ADD CONSTRAINT `fk2_status_messages` FOREIGN KEY (`user_id`) REFERENCES users(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE');
              }catch( PDOException $e ){
                database_errors($e);
              }
            }elseif( (int) $result->table_columns > 0 ){
              //if the table does exist, verify that all the columns for that table exist
              verify_tables($app->db,'status_messages',array('social_site'=>'TINYINT','user_id'=>'INT(11), ADD CONSTRAINT FOREIGN KEY fk2_status_messages(`user_id`) REFERENCES users(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE',$app->db_name));
            }
          }catch( PDOException $e ){
            database_errors($e);
          }

          //check if a social message type table exists
          try{
            $social_sites = $app->db->query('SELECT COUNT(*) as table_columns FROM information_schema.tables WHERE table_schema = "'.$app->db_name.'" AND table_name = "social_sites"');
            $result = $social_sites->fetch();
            if( (int) $result->table_columns === 0 ){
              try{
                $create_social_sites = $app->db->exec('CREATE TABLE IF NOT EXISTS social_sites (
                  site_id TINYINT NOT NULL AUTO_INCREMENT,
                  site_name VARCHAR(320),
                  PRIMARY KEY  (`site_id`))
                  ENGINE = InnoDB 
                  DEFAULT CHARACTER SET = utf8 
                  COLLATE = utf8_unicode_ci 
                  COMMENT = "default social networks user can post trks.it link on";');
                $default_sites = $app->db->exec('INSERT INTO social_sites VALUES (NULL, "Facebook"), (NULL, "Google+"), (NULL, "LinkedIn"), (NULL, "Twitter")');
              }catch( PDOException $e ){
                database_errors($e);
              }
            }elseif( (int) $result->table_columns > 0 ){

            }
          }catch( PDOException $e ){
            database_errors($e);
          }
          //update database updates table with md5sum
          $update_1 = $app->db->exec('INSERT INTO db_updates (update_hash, update_date) VALUES ("5faMDDtgCDKwWtx2dp9scvsLNMrJ9MDk", NOW())');
        }

        if( !in_array_r('2bZqMxb9J6uKkHcAJdSTvfmgy4ce3KLb',$hashes) ){
          $app->db->beginTransaction();

          try{
            $app->db->exec('ALTER TABLE status_messages MODIFY message TEXT');
            //update database updates table with md5sum
          $update_2 = $app->db->exec('INSERT INTO db_updates (update_hash, update_date) VALUES ("2bZqMxb9J6uKkHcAJdSTvfmgy4ce3KLb", NOW())');

            $app->db->commit();
          }catch( PDOException $e ){
            $app->db->rollBack();
            database_errors($e);
          }
        }

        if( !in_array_r('efa7fe7a5551ac44eb0279c3a9142bac',$hashes) ){
          $app->db->beginTransaction();

          try{
            //add user id to script table to identify who made a script
            $check_column = $app->db->query('SELECT COUNT(*) as table_columns FROM information_schema.columns WHERE table_schema = "'.$app->db_name.'" AND table_name = "scripts" AND column_name = "user_id"');
            $result = $check_column->fetch();
            if( (int) $result->table_columns === 0 ){
              $app->db->exec('ALTER TABLE scripts ADD user_id INT(11), ADD CONSTRAINT FOREIGN KEY fk1_scripts(user_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE');
            }

            //delete scripts_to_urls assignment_id (mispelled as assigment_id) from scripts_to_urls table
            $check_column = $app->db->query('SELECT COUNT(*) as table_columns FROM information_schema.columns WHERE table_schema = "'.$app->db_name.'" AND table_name = "scripts" AND column_name = "assigment_id"');
            $result = $status_table->fetch();
            if( (int) $result->table_columns != 0 ){
              $app->db->exec('ALTER TABLE scripts_to_urls MODIFY assigment_id INT NOT NULL');
              $app->db->exec('ALTER TABLE scripts_to_urls DROP PRIMARY KEY');
              $app->db->exec('ALTER TABLE scripts_to_urls DROP assigment_id');
            }
            
            //create a unique index for scripts_to_urls table linking script and urls together
            $app->db->exec('ALTER TABLE scripts_to_urls ADD UNIQUE INDEX ui1_scripts_to_urls(script_id,url_id)');

            //update database updates table with md5sum
            $update_3 = $app->db->exec('INSERT INTO db_updates (update_hash, update_date) VALUES ("efa7fe7a5551ac44eb0279c3a9142bac", NOW())');

            $app->db->commit();
          }catch( PDOException $e ){
            database_errors($e);
          }
        }

        if( !in_array_r('d8875f8e4bfd02a31a6020e6376b96a5',$hashes) ){
          $app->db->beginTransaction();

          try{
            //add the user id who created the group
            $check_column = $app->db->query('SELECT COUNT(*) as table_columns FROM information_schema.columns WHERE table_schema = "'.$app->db_name.'" AND table_name = "groups" AND column_name = "created_by"');
            $result = $check_column->fetch();
            if( (int) $result->table_columns === 0 ){
              $app->db->exec('ALTER TABLE groups ADD created_by INT(11), ADD CONSTRAINT FOREIGN KEY fk1_groups(created_by) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE');
              //select the ID of the admin user type
              $get_admin = $app->db->query('SELECT type_id FROM users_types WHERE type_name = "admin" LIMIT 1');
              $get_admin_result = $get_admin->fetch();
              if( $get_admin_result != false ){
                $admin_id = $get_admin_result->type_id;

                //set the default values for the new fields
                $group_owner = $app->db->query('SELECT user_id FROM users WHERE user_type = '.$admin_id);
                $result = $group_owner->fetch();

                if( $result != false ){
                  $group_owner = $result;
                  //set the created by field to the ID of the first admin user
                  $app->db->exec('UPDATE groups SET created_by = '.$group_owner->user_id);
                }
              }
            }

            //add a created on date to groups
            $check_column = $app->db->query('SELECT COUNT(*) as table_columns FROM information_schema.columns WHERE table_schema = "'.$app->db_name.'" AND table_name = "groups" AND column_name = "date_created"');
            $result = $check_column->fetch();
            if( (int) $result->table_columns === 0 ){
              $app->db->exec('ALTER TABLE groups ADD date_created DATE NULL');
              //set the default value for date_created column to NOW
              $app->db->exec('UPDATE groups SET date_created = NOW()');
            }

            //update database updates table with md5sum
            $update_4 = $app->db->exec('INSERT INTO db_updates (update_hash, update_date) VALUES ("d8875f8e4bfd02a31a6020e6376b96a5", NOW())');

            $app->db->commit();
          }catch( PDOException $e ){
            $app->db->rollBack();
            database_errors($e);
          }
        }
        
        if( !in_array_r('5A74CP09R87U1jYVA8xdS7Gk8YRI23Ww',$hashes) ){
	     	
			   $app->db->beginTransaction();
	     	
  	     	try{
    	     	//add party column to short_urls table
            $check_column = $app->db->query('SELECT COUNT(*) as table_columns FROM information_schema.columns WHERE table_schema = "'.$app->db_name.'" AND table_name = "short_urls" AND column_name = "party"');
            $result = $check_column->fetch();   
            
            //if the column is not there, let's add it.
            if( (int) $result->table_columns === 0 ){
              $app->db->exec('ALTER TABLE short_urls ADD party VARCHAR(10)');
            }
            
            //update database updates table with md5sum
  				  $update_5 = $app->db->exec('INSERT INTO db_updates (update_hash, update_date) VALUES ("5A74CP09R87U1jYVA8xdS7Gk8YRI23Ww", NOW())');
  	            
            //commit the change
            $app->db->commit();		     	
  	     	}catch( PDOException $e ){
            $app->db->rollBack();
          	database_errors($e);
          }
        }

        if( !in_array_r('52krE8ewGKnWdFN7naTweA2Z7BTKArVa',$hashes) ){
        
          $app->db->beginTransaction();
        
          try{
            //add a unique index to users groups to get rid of duplicate keys where a user belongs to a group multiple times
            $users_groups_index = $app->db->exec('ALTER IGNORE TABLE users_groups ADD UNIQUE INDEX ui1_users_groups(user_id,group_id)');
            
            //update database updates table with md5sum
            $update_6 = $app->db->exec('INSERT INTO db_updates (update_hash, update_date) VALUES ("52krE8ewGKnWdFN7naTweA2Z7BTKArVa", NOW())');
                
            //commit the change
            $app->db->commit();         
          }catch( PDOException $e ){
            $app->db->rollBack();
            database_errors($e);
          }
        }

        if( !in_array_r('c7d072616f5831c9bf2c64c91fd59d40',$hashes) ){
        
          $app->db->beginTransaction();
        
          try{
            //add the term and content columns to short_urls table
            $users_groups_index = $app->db->exec('ALTER TABLE short_urls ADD COLUMN term VARCHAR(255) NULL AFTER campaign, ADD COLUMN content VARCHAR(255) NULL AFTER term');

            //change the COLLATE type of the user types table
            $table_collate = $app->db->exec('ALTER TABLE users_types CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci');

            //create table to hold ad network URLS
            $ad_network_urls = $app->db->exec('CREATE TABLE ad_network_urls(
              anu_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              anu_url VARCHAR(320) NOT NULL,
              source VARCHAR(255) NULL,
              medium VARCHAR(255) NULL,
              campaign VARCHAR(255) NULL ,
              term VARCHAR(255) NULL,
              content VARCHAR(255) NULL,
              date_created DATETIME NULL,
              short_id INT UNSIGNED NULL,
              user_id INT UNSIGNED NOT NULL,
              CONSTRAINT fk1_adu_to_short_urls FOREIGN KEY (short_id) REFERENCES short_urls(short_id) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT fk2_adu_to_users FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
              PRIMARY KEY (anu_id) )
              ENGINE = InnoDB 
              DEFAULT CHARACTER SET = utf8 
              COLLATE = utf8_unicode_ci 
              COMMENT = "table to hold urls used for ad networks"');
            
            //update database updates table with md5sum
            $update_7 = $app->db->exec('INSERT INTO db_updates (update_hash, update_date) VALUES ("c7d072616f5831c9bf2c64c91fd59d40", NOW())');
                
            //commit the change
            $app->db->commit();         
          }catch( PDOException $e ){
            $app->db->rollBack();
            database_errors($e);
          }
        }

      }
        
    }catch( PDOException $e ){
      database_errors($e);
    }

  }
});

 *****************************/