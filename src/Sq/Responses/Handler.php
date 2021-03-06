<?php

namespace Sq\Responses;

use PDO;
use Sq\DB;
use Sq\Exe;
use Sq\ResponseFoundation;

require_once __DIR__."/msg_definer.php";

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @version 0.0.1
 * @package \Sq\Responses
 */
class Handler extends ResponseFoundation
{
	/**
	 * @return void
	 */
	public function handle(): void
	{
		if (
			isset(
				$this->b->d["message"]["reply_to_message"]["from"]["is_bot"],
				$this->b->d["message"]["text"]
			) &&
			$this->b->d["message"]["reply_to_message"]["from"]["is_bot"]
		) {

			$text = $this->b->d["message"]["text"];

			if (isset($this->b->d["message"]["reply_to_message"]["text"])) {
				$rdt = $this->b->d["message"]["reply_to_message"]["text"];
			} else if (isset($this->b->d["message"]["reply_to_message"]["caption"])) {
				$rdt = trim($this->b->d["message"]["reply_to_message"]["caption"]);
				if ($rdt === "") {
					return;
				}
			} else {
				return;
			}

			if (substr($rdt, 0, 6) === "Follow") {
				$rdt = explode("\n", $rdt, 2);
				$rdt = $rdt[0];
			}

			switch ($rdt) {
				case "Follow our Medium":
					if (
						(!filter_var($text, FILTER_VALIDATE_URL)) ||
						(!preg_match("/medium\.com/Usi", $text))
					) {

						Exe::sendMessage(
							[
								"chat_id" => $this->b->d["message"]["chat"]["id"],
								"text" => "<b>Invalid facebook URL!</b>",
								"reply_to_message_id" => $this->b->d["message"]["message_id"],
								"parse_mode" => "HTML"
							]
						);

						$twitterUrl = /*htmlspecialchars*/(file_get_contents(BASEPATH."/storage/redirector/twitter.txt")/*, ENT_QUOTES, "UTF-8"*/);

						Exe::sendMessage(
							[
								"chat_id" => $this->b->d["message"]["chat"]["id"],
								"text" => "Follow our Medium\n<a href=\"{$mediumUrl}\">Click HERE to go to our Medium.</a>\n<b>Please send me your Medium's Account link to continue</b>\n\n<b>Reply to this message!</b>",
								"reply_to_message_id" => $this->b->d["message"]["message_id"],
								"parse_mode" => "HTML",
								"reply_markup" => json_encode(["force_reply" => true])
							]
						);
						return;
					}
					$pdo = DB::pdo();

					$pdo->prepare(
						"UPDATE `users` SET `medium_link` = :medium_link WHERE id = :user_id LIMIT 1;"
					)->execute(
						[
							":medium_link" => $text,
							":user_id" => $this->b->d["message"]["from"]["id"]
						]
					);
					addPoint(5, $this->b->d["message"]["from"]["id"]);
					Exe::sendMessage(
						[
							"chat_id" => $this->b->d["message"]["chat"]["id"],
							"text" => "<b>You Medium link has been set to:</b> {$text}",
							"reply_to_message_id" => $this->b->d["message"]["message_id"],
							"parse_mode" => "HTML",
						]
					);

					$st = $pdo->prepare("SELECT `email` FROM `users` WHERE `id` = :user_id LIMIT 1;");
					$st->execute([":user_id" => $this->b->d["message"]["from"]["id"]]);
					if ($st = $st->fetch(PDO::FETCH_NUM)) {
						if ($st[0]) {
							$r = "Send /help to show other commands!";
						} else {
							$r = "Send /submit to continue";
						}
					} else {
						$r = "Send /submit to continue";
					}

					Exe::sendMessage(
						[
							"chat_id" => $this->b->d["message"]["chat"]["id"],
							"text" => $r,
							"reply_to_message_id" => $this->b->d["message"]["message_id"],
						]
					);

					unset($pdo);

					break;


				case "Follow & Like Our Fanspage":
					if (
						(!filter_var($text, FILTER_VALIDATE_URL)) ||
						(!preg_match("/^(https?\:\/\/)?((www|m|web|mobile)\.)?(facebook)\.com\/.+/Usi", $text))
					) {

						Exe::sendMessage(
							[
								"chat_id" => $this->b->d["message"]["chat"]["id"],
								"text" => "<b>Invalid facebook URL!</b>",
								"reply_to_message_id" => $this->b->d["message"]["message_id"],
								"parse_mode" => "HTML"
							]
						);

						$twitterUrl = /*htmlspecialchars*/(file_get_contents(BASEPATH."/storage/redirector/twitter.txt")/*, ENT_QUOTES, "UTF-8"*/);

						Exe::sendMessage(
							[
								"chat_id" => $this->b->d["message"]["chat"]["id"],
								"text" => "Follow & Like Our Fanspage\n<a href=\"{$facebookUrl}\">Click HERE to go to our Facebook Account.</a>\n<b>Please send me your Facebook's Account link to continue</b>\n\n<b>Reply to this message!</b>",
								"reply_to_message_id" => $this->b->d["message"]["message_id"],
								"parse_mode" => "HTML",
								"reply_markup" => json_encode(["force_reply" => true])
							]
						);
						return;
					}
					DB::pdo()->prepare(
						"UPDATE `users` SET `facebook_link` = :facebook_link WHERE id = :user_id LIMIT 1;"
					)->execute(
						[
							":facebook_link" => $text,
							":user_id" => $this->b->d["message"]["from"]["id"]
						]
					);
					addPoint(4, $this->b->d["message"]["from"]["id"]);
					Exe::sendMessage(
						[
							"chat_id" => $this->b->d["message"]["chat"]["id"],
							"text" => "<b>You Facebook link has been set to:</b> {$text}",
							"reply_to_message_id" => $this->b->d["message"]["message_id"],
							"parse_mode" => "HTML",
							"reply_markup" => json_encode(
								[
									"inline_keyboard" => [
										[
											[
												"text" => "Follow & Like Our Medium",
												"callback_data" => "mdd"
											]
										],
										[
											[
												"text" => "Skip Follow Our Medium (Go to the next section)",
												"callback_data" => "sk_mdd",
											]
										]
									]
								]
							)
						]
					);

					break;

				case "Follow & Retweet Our Twitter":
					if (
						(!filter_var($text, FILTER_VALIDATE_URL)) ||
						(!preg_match("/^https?\:\/\/twitter\.com\/.+/", $text))
					) {

						Exe::sendMessage(
							[
								"chat_id" => $this->b->d["message"]["chat"]["id"],
								"text" => "<b>Invalid twitter URL!</b>",
								"reply_to_message_id" => $this->b->d["message"]["message_id"],
								"parse_mode" => "HTML"
							]
						);

						$twitterUrl = /*htmlspecialchars*/(file_get_contents(BASEPATH."/storage/redirector/twitter.txt")/*, ENT_QUOTES, "UTF-8"*/);

						Exe::sendMessage(
							[
								"chat_id" => $this->b->d["message"]["chat"]["id"],
								"text" => "Follow & Retweet Our Twitter\n<a href=\"{$twitterUrl}\">Click HERE to go to our Twitter Account.</a>\n<b>Please send me your Twitter's Account link to continue!</b>\n\n<b>Reply to this message!</b>",
								"reply_to_message_id" => $this->b->d["message"]["message_id"],
								"parse_mode" => "HTML",
								"reply_markup" => json_encode(["force_reply" => true])
							]
						);
						return;
					}
					DB::pdo()->prepare(
						"UPDATE `users` SET `twitter_link` = :twitter_link WHERE id = :user_id LIMIT 1;"
					)->execute(
						[
							":twitter_link" => $text,
							":user_id" => $this->b->d["message"]["from"]["id"]
						]
					);
					addPoint(3, $this->b->d["message"]["from"]["id"]);
					Exe::sendMessage(
						[
							"chat_id" => $this->b->d["message"]["chat"]["id"],
							"text" => "<b>You twitter link has been set to:</b> {$text}",
							"reply_to_message_id" => $this->b->d["message"]["message_id"],
							"parse_mode" => "HTML",
							"reply_markup" => json_encode(
								[
									"inline_keyboard" => [
										[
											[
												"text" => "Follow & Like Our Fanspage",
												"callback_data" => "fbd"
											]
										]
									]
								]
							)
						]
					);
					break;

				case "What is your wallet address?\n\nReply to this message!":
					$pdo = DB::pdo();
					$st = $pdo->prepare("SELECT `wallet` FROM `users` WHERE `id` = :user_id LIMIT 1;");
					$st->execute([":user_id" => $this->b->d["message"]["from"]["id"]]);
					$st = $st->fetch(PDO::FETCH_NUM);

					if (!$st[0]) {

						$rep = "Successfully set a new wallet address!\n\n<b>Your wallet address has been set to:</b> {$text}\n\n".
						"Other commands:\n".
						"/info\t\tShow your information\n".
						"/set_wallet\t set/update your wallet address\n".
						"/set_email\t set/update your email address";

						$pdo->prepare(
							"UPDATE `users` SET `joined_at` = :joined_at WHERE `id` = :user_id LIMIT 1;"
						)->execute(
							[
								":joined_at" => date("Y-m-d H:i:s"),
								":user_id" => $this->b->d["message"]["from"]["id"]
							]
						);

						$rd = json_encode(
							[
								"keyboard" => [
									[
										[
											"text" => "Balance \xf0\x9f\x92\xb0",
										],
									],
									[	

										[
											"text" => "Support \xe2\x98\x8e\xef\xb8\x8f"
										]
									],
									[
										[
											"text" => "Tasks \xe2\x9a\x94\xef\xb8\x8f"
										]
									],
									[
										[
											"text" => "Buy Token \xf0\x9f\x92\xb4"
										]
									],
									[
										[
											"text" => "Referral Link \xf0\x9f\x91\xa5",
										],
										[
											"text" => "Social Media \xf0\x9f\x8c\x8d"
										]
									]
								]
							]
						);

						$stq = $pdo->prepare("SELECT `user_id`,`referral_id` FROM `referred_users` WHERE `user_id` = :id LIMIT 1;");
						$stq->execute([":id" => $this->b->d["message"]["from"]["id"]]);

						if ($stq = $stq->fetch(PDO::FETCH_NUM)) {
							$pdo->prepare(
								"UPDATE `users` SET `balance` = `balance` + 10000 WHERE `id` = :id LIMIT 1;"
							)->execute([":id" => $stq[1]]);
							$name = htmlspecialchars($this->b->d["message"]["from"]["first_name"], ENT_QUOTES, "UTF-8");
							Exe::sendMessage(
								[
									"chat_id" => $stq[1],
									"text" => "<a href=\"tg://user?id={$this->b->d["message"]["from"]["id"]}\">{$name}</a> has joined through your referral link!\n\nYour VENO balance has been added!\n\n+5000 VENO",
									"parse_mode" => "HTML",
									"reply_markup" => $rd
								]
							);
						}

						
						
					} else {
						if ($st[0] === $text) {
							$rep = "Wallet address could not be changed because you just sent the same wallet address!\n\nCurrent wallet address which linked to your telegram account is <code>".htmlspecialchars($st[0], ENT_QUOTES, "UTF-8")."</code>\n\n".
								"Other commands:\n".
								"/info\t\tShow your information\n".
								"/set_wallet\t set/update your wallet address\n".
								"/set_email\t set/update your email address";
							$noUpdate = 1;
						} else {
							$rep =
								"Successfully update your wallet address!\n\nYour wallet address <code>".htmlspecialchars($st[0], ENT_QUOTES, "UTF-8")."</code> is now deleted from our database!\n\n<b>Your wallet address has been set to:</b> <code>".htmlspecialchars($st[0], ENT_QUOTES, "UTF-8")."</code> \n\n".
								"Other commands:\n".
								"/info\t\tShow your information\n".
								"/set_wallet\t set/update your wallet address\n".
								"/set_email\t set/update your email address";
						}
					}

					if (!isset($noUpdate)) {
						$st = $pdo->prepare("UPDATE `users` SET `wallet`=:wallet WHERE `id` = :user_id LIMIT 1;");	
						$st->execute([":wallet" => $text, ":user_id" => $this->b->d["message"]["from"]["id"]]);
					}

					unset($st, $pdo);

					$d = [
							"text" => $rep,
							"chat_id" => $this->b->d["message"]["from"]["id"],
							"reply_to_message_id" => $this->b->d["message"]["message_id"],
							"parse_mode" => "HTML"
						];

					if (isset($rd)) {
						$d["reply_markup"] = $rd;
						unset($rd);
					}

					Exe::sendMessage($d);
				break;

				case "To continue, please send the captcha below!\n\nReply to this message!":

					if (!file_exists(BASEPATH."/storage/captcha/{$this->b->d['message']['from']['id']}.txt")) {
						Exe::sendMessage(
							[
								"text" => "Invalid command!\n\nSend /help to see all commands",
								"chat_id" => $this->b->d["message"]["from"]["id"],
								"reply_to_message_id" => $this->b->d["message"]["message_id"],
							]
						);
						return;
					}

					if (file_get_contents(BASEPATH."/storage/captcha/{$this->b->d['message']['from']['id']}.txt") === $text) {
						

						Exe::sendMessage(
							[
								"chat_id" => $this->b->d["message"]["chat"]["id"],
								"text" => "Captcha OK",
								"reply_to_message_id" => $this->b->d["message"]["message_id"],
							]
						);

						unlink(BASEPATH."/storage/captcha/{$this->b->d['message']['from']['id']}.txt");

						Exe::sendMessage(
							[
								"chat_id" => $this->b->d["message"]["chat"]["id"],
								"text" => "What is your email address?\n\nReply to this message!",
								"reply_to_message_id" => $this->b->d["message"]["message_id"],
								"reply_markup" => json_encode(["force_reply" => true])
							]
						);

						$pdo = DB::pdo();

						$st = $pdo->prepare("UPDATE `sessions` SET `state` = '1' WHERE `user_id` = :user_id LIMIT 1;");
						$st->execute([":user_id" => $this->b->d["message"]["from"]["id"]]);

						unset($st, $pdo);

					} else {

						require_once __DIR__."/make_captcha.php";
						file_put_contents(
							BASEPATH."/storage/captcha/{$this->b->d['message']['from']['id']}.txt", 
							makeCaptcha(BASEPATH."/public/captcha_d/{$this->b->d['message']['from']['id']}.png")
						);

						Exe::sendMessage(
							[
								"chat_id" => $this->b->d["message"]["chat"]["id"],
								"text" => "Invalid captcha response",
								"reply_to_message_id" => $this->b->d["message"]["message_id"],
							]
						);

						Exe::sendPhoto(
							[
								"chat_id" => $this->b->d["message"]["from"]["id"],
								"photo" => (
									"https://bot.cryptoveno.com/captcha_d/{$this->b->d['message']['from']['id']}.png?std=".time()."&w=".rand()
								),
								"caption" => "To continue, please send the captcha below!\n\nReply to this message!",
								"reply_markup" => json_encode(["force_reply" => true])
							]
						);

					}
				break;


				case "What is your email address?\n\nReply to this message!":
				case "Invalid email address!\n\nPlease reply this message with a valid email address!":
					if (filter_var($text, FILTER_VALIDATE_EMAIL)) {

						$text = strtolower($text);

						$pdo = DB::pdo();
						$st = $pdo->prepare("SELECT `email` FROM `users` WHERE `id` = :user_id LIMIT 1;");
						$st->execute([":user_id" => $this->b->d["message"]["from"]["id"]]);
						$st = $st->fetch(PDO::FETCH_NUM);

						if (!$st[0]) {
							$rep = "Successfully set a new email address!\n\n<b>Your email address has been set to:</b> {$text}\n\nPlease set your wallet address by send /set_wallet";
						} else {
							if ($st[0] === $text) {
								$rep = "Email address could not be changed because you just sent the same email address!\n\nCurrent email address which linked to your telegram account is {$st[0]}\n\n".
									"Other commands:\n".
									"/info\t\tShow your information\n".
									"/set_wallet\t set/update your wallet address\n".
									"/set_email\t set/update your email address";
								$noUpdate = 1;
							} else {
								$rep =
									"Successfully update your email address!\n\nYour email {$st[0]} is now deleted from our database!\n\n<b>Your email address has been set to:</b> {$text}\n\n".
									"Other commands:\n".
									"/info\t\tShow your information\n".
									"/set_wallet\t set/update your wallet address\n".
									"/set_email\t set/update your email address";
							}
						}

						if (!isset($noUpdate)) {
							$st = $pdo->prepare("UPDATE `users` SET `email`=:email WHERE `id` = :user_id LIMIT 1;");	
							$st->execute([":email" => $text, ":user_id" => $this->b->d["message"]["from"]["id"]]);
						}

						unset($st, $pdo);

						Exe::sendMessage(
							[
								"text" => $rep,
								"chat_id" => $this->b->d["message"]["from"]["id"],
								"reply_to_message_id" => $this->b->d["message"]["message_id"],
								"parse_mode" => "HTML"
							]
						);

					} else {
						Exe::sendMessage(
							[
								"text" => __INVALID_EMAIL_ADDRESS,
								"chat_id" => $this->b->d["message"]["from"]["id"],
								"reply_to_message_id" => $this->b->d["message"]["message_id"],
								"parse_mode" => "HTML",
								"reply_markup" => json_encode([
									"force_reply" => true
								])
							]
						);
					}
				break;
			}
		} else {
			$st = DB::pdo()->prepare("SELECT `facebook_link` FROM `users` WHERE `id` = :user_id LIMIT 1;");
			$st->execute([":user_id" => $this->b->d["message"]["from"]["id"]]);
			if ($st = $st->fetch(PDO::FETCH_NUM)) {
				if ($st[0]) {
					Exe::sendMessage(
						[
							"text" => "Invalid command!\n\nSend /help to see all commands",
							"chat_id" => $this->b->d["message"]["from"]["id"],
							"reply_to_message_id" => $this->b->d["message"]["message_id"],
						]
					);
					return;
				}
			}
			(new Start($this->b))->start();
		}
	}
}
