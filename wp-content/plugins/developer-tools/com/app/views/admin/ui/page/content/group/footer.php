  		<?php if( $view['action'] ) : ?>
  			<div class="form_submit_button <?php print $view['action'] ?>">
  				<input type="submit" value="<?php print $view['button_text'] ?>" class="button-primary">
  			</div>
  		<?php endif; ?>
  	</div><!-- .inside -->
  </div><!-- .postbox -->
<?php if( $view['end_form'] ) : ?>
  </form>
<?php endif ?>