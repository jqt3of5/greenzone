using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Net;
using System.Net.Sockets;
using System.Runtime.InteropServices;
using System.Runtime.Serialization;
using System.Runtime.Serialization.Json;
using System.Text;
using System.Threading;
using System.Xml;

namespace SpotifyControl
{
	class Program
	{


		internal class SpotifyControl
		{
			private WebClient _webClient; 
			private string _oauth;
			private string _cfid;
			private IntPtr _spotifyWindowHandle;

			public SpotifyControl()
			{
				//emulate the embed code [NEEDED]
				_webClient = new WebClient();
				_webClient.Headers.Add("Origin", "https://embed.spotify.com");
				_webClient.Headers.Add("Referer", "https://embed.spotify.com/?uri=spotify:track:5Zp4SWOpbuOdnsxLqwgutt");
			}

			public void Start()
			{
				_oauth = GetOAuth();
				_cfid = GetCFID(_oauth);
				_spotifyWindowHandle = Win32.FindWindow("SpotifyMainWindow", null);

				if (_spotifyWindowHandle == null)
				{
					throw new Exception("Could not find the Spotify window. Is Spotify running?");
				}
			}

			#region ControlMethods

			public void Next()
			{
				if (_spotifyWindowHandle != null)
					Win32.SendMessage(_spotifyWindowHandle, Win32.Constants.WM_APPCOMMAND, IntPtr.Zero, new IntPtr((long)SpotifyAction.NextTrack));
			}

			public void Previous()
			{
				if (_spotifyWindowHandle != null)
					Win32.SendMessage(_spotifyWindowHandle, Win32.Constants.WM_APPCOMMAND, IntPtr.Zero, new IntPtr((long)SpotifyAction.PreviousTrack));
			}

			public void VolumeUp(int n = 1)
			{
				if (_spotifyWindowHandle != null)
				{
					for (int i = 0; i < n; ++i)
						Win32.SendMessage(_spotifyWindowHandle, Win32.Constants.WM_APPCOMMAND, IntPtr.Zero, new IntPtr((long)SpotifyAction.VolumeUp));	
				}
			}

			public void VolumeDown(int n = 1)
			{
				if (_spotifyWindowHandle != null)
				{

					for (int i = 0; i < n; ++i)
						Win32.SendMessage(_spotifyWindowHandle, Win32.Constants.WM_APPCOMMAND, IntPtr.Zero,
						                  new IntPtr((long) SpotifyAction.VolumeDown));
				}
			}

			public void SetVolume(int volume)
			{
				var status = Status();

				int diff =(int)(volume - status.volume*100);

				if (diff > 0)
				{
					VolumeUp(diff/5); // each volume tick is 5 percent TODO: CHECK if that's true!
				}
				else
				{
					VolumeDown(diff/5);
				}
			}

			public void Mute()
			{
				Win32.SendMessage(_spotifyWindowHandle, Win32.Constants.WM_APPCOMMAND, IntPtr.Zero,new IntPtr((long)SpotifyAction.Mute));
			}
			public  string Play(string uri)
			{
				string response = SpotifyRequest("remote/play.json?uri=" + uri, _oauth, _cfid, -1);
				return ParseStatusResponse(response).ToString();
			}

			public string Pause()
			{
				string response = SpotifyRequest("remote/pause.json?pause=true", _oauth, _cfid, -1);
				return ParseStatusResponse(response).ToString();
			}

			public string Resume()
			{
				string response = SpotifyRequest("remote/pause.json?pause=false", _oauth, _cfid, -1);
				return ParseStatusResponse(response).ToString();
			}

			public void Stop()
			{
				Win32.SendMessage(_spotifyWindowHandle, Win32.Constants.WM_APPCOMMAND, IntPtr.Zero,new IntPtr((long)SpotifyAction.Stop));
			}
	
			public string GetJSONStatus()
			{
				return SpotifyRequest("remote/status.json", _oauth, _cfid, -1);
			}
			
			public StatusResponseJSON Status()
			{
				string response = GetJSONStatus();
				return ParseStatusResponse(response);
			}

			private StatusResponseJSON ParseStatusResponse(string response)
			{
				DataContractJsonSerializer json = new DataContractJsonSerializer(typeof(List<StatusResponseJSON>));

				using (var stream = new MemoryStream(Encoding.UTF8.GetBytes(response)))
				{
					var status = json.ReadObject(stream) as List<StatusResponseJSON>;
					return status[0];
				}
			}
			#endregion

			#region Info Methods
			/// <summary>
			/// Get a link to the 640x640 cover art image of a spotify album
			/// </summary>
			/// <param name="uri">The Spotify album URI</param>
			/// <returns></returns>
			public static string GetArt(string uri)
			{
				try
				{
					string raw = new WebClient().DownloadString("http://open.spotify.com/album/" + uri.Split(new string[] { ":" }, StringSplitOptions.None)[2]);
					raw = raw.Replace("\t", ""); ;
					string[] lines = raw.Split(new string[] { "\n" }, StringSplitOptions.None);
					foreach (string line in lines)
					{
						if (line.StartsWith("<meta property=\"og:image\""))
						{
							string[] l = line.Split(new string[] { "/" }, StringSplitOptions.None);
							return "http://o.scdn.co/640/" + l[4].Replace("\"", "").Replace(">", "");
						}
					}
				}
				catch
				{
					return "";
				}
				return "";
			}
			#endregion

			#region Internal Classes
			[DataContract]
			internal class TokenJSON
			{
				[DataMember] public string token = String.Empty;
			}

			[DataContract]
			internal class ResourceJSON
			{
				[DataMember]
				public string name = String.Empty;
				[DataMember]
				public string uri = String.Empty;
			}
			
			[DataContract]
			internal class Track
			{
				[DataMember]
				public ResourceJSON track_resource = null;
				[DataMember]
				public ResourceJSON artist_resource = null;
				[DataMember]
				public ResourceJSON album_resource = null;
				[DataMember]
				public int length = 0;
			}
			[DataContract]
			internal class StatusResponseJSON
			{
				[DataMember] public int version = 0;
				[DataMember] public string client_version = String.Empty;
				[DataMember] public bool playing = false;
				[DataMember] public bool shuffle = false;
				[DataMember] public bool repeat = false;
				[DataMember] public Track track = null;
				[DataMember] public double playing_position = -1;
				[DataMember] public double volume = -1;

				public override string ToString()
				{
					return String.Format("{0} - {1}, {2}s", track.track_resource.name, track.artist_resource.name, playing_position);
				}
			}


			public enum SpotifyAction : long
			{
				Play = 917504,
				Pause = 917504,
				Mute = 524288,
				VolumeDown = 589824,
				VolumeUp = 655360,
				SetVolume,
				Stop = 851968,
				PreviousTrack = 786432,
				NextTrack = 720896,
			}

			internal class Win32
			{
				
				[DllImport("user32.dll", CharSet = CharSet.Auto, SetLastError = false)]
				internal static extern IntPtr SendMessage(IntPtr hWnd, uint Msg, IntPtr wParam, IntPtr lParam);

				[DllImport("user32.dll", EntryPoint = "FindWindow")]
				internal static extern IntPtr FindWindow(string lp1, string lp2);

				internal class Constants
				{
					internal const uint WM_APPCOMMAND = 0x0319;
				}
			}

			#endregion

			#region Private Methods
		
			/// <summary>
			/// Recieves a OAuth key from the Spotify site
			/// </summary>
			/// <returns></returns>
			private static string GetOAuth()
			{
				string raw = new WebClient().DownloadString("https://embed.spotify.com/openplay/?uri=spotify:track:5Zp4SWOpbuOdnsxLqwgutt");
				raw = raw.Replace(" ", "");
				string[] lines = raw.Split(new string[] { "\n" }, StringSplitOptions.None);
				foreach (string line in lines)
				{
					if (line.StartsWith("tokenData"))
					{
						string[] l = line.Split(new string[] { "'" }, StringSplitOptions.None);
						return l[1];
					}
				}

				throw new Exception("Could not find OAuth token");
			}
			
			
			private string GetCFID(string oauth)
			{
				string response = SpotifyRequest("simplecsrf/token.json", oauth, null, -1);
				DataContractJsonSerializer json = new DataContractJsonSerializer(typeof(List<TokenJSON>));

				using (var stream = new MemoryStream(Encoding.UTF8.GetBytes(response)))
				{
					var token = json.ReadObject(stream) as List<TokenJSON>;
					return token[0].token;
				}
			}

			private string SpotifyRequest(string request, string oauth, string cfid, int wait)
			{
				var timeStamp = Convert.ToInt32((DateTime.UtcNow - new DateTime(1970, 1, 1, 0, 0, 0)).TotalSeconds);
				string parameters = "?&ref=&cors=&_=" + timeStamp;
				if (request.Contains("?"))
				{
					parameters = parameters.Substring(1);
				}

				if (!String.IsNullOrEmpty(oauth))
				{
					parameters += "&oauth=" + oauth;
				}
				if (!String.IsNullOrEmpty(cfid))
				{
					parameters += "&csrf=" + cfid;
				}

				if (wait != -1)
				{
					parameters += "&returnafter=" + wait;
					parameters += "&returnon=login%2Clogout%2Cplay%2Cpause%2Cerror%2Cap";
				}

				string a = "http://localhost:4380/" + request + parameters;
				string derp = "";
				try
				{
					derp = _webClient.DownloadString(a);
					derp = "[ " + derp + " ]";
				}
				catch (Exception z)
				{
					//perhaps spotifywebhelper isn't started (happens sometimes)
					if (Process.GetProcessesByName("SpotifyWebHelper").Length < 1)
					{
						try
						{
							System.Diagnostics.Process.Start(Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData) + "\\Spotify\\Data\\SpotifyWebHelper.exe");
						}
						catch (Exception dd)
						{
							throw new Exception("Could not launch SpotifyWebHelper. Your installation of Spotify might be corrupt or you might not have Spotify installed", dd);
						}

						return SpotifyRequest(request, oauth, cfid, -1);
					}
					//spotifywebhelper is running but we still can't connect, wtf?!
					else throw new Exception("Unable to connect to SpotifyWebHelper", z);
				}
				return derp;
			}
			#endregion

		}

		internal class SpotifySearch
		{
			private const string baseURL = "http://ws.spotify.com/search/1/";
			private WebClient _webClient;
			
			public SpotifySearch()
			{
				_webClient = new WebClient();
			}
			
			#region Data Classes

			public class SpotifyObjectDoc
			{
				public string name;
				public string url;
			}
			public class ArtistDoc : SpotifyObjectDoc
			{
			}
			public class AlbumDoc : SpotifyObjectDoc
			{
				public ArtistDoc artist;
			}
			public class TrackDoc : SpotifyObjectDoc
			{
				public ArtistDoc artist;
				public AlbumDoc album;
			}
			enum SearchType
			{
				ARTIST, ALBUM, TRACK
			}
			#endregion

			#region Search Methods
			public List<ArtistDoc> SearchArtist(string query)
			{
				return Search(query, SearchType.ARTIST).Select((doc) => doc as ArtistDoc).ToList();
			}
			public List<AlbumDoc> SearchAlbum(string query)
			{
				return Search(query, SearchType.ALBUM).Select((doc) => doc as AlbumDoc).ToList();
			}

			public List<TrackDoc> SearchTrack(string query)
			{
				return Search(query, SearchType.TRACK).Select((doc) => doc as TrackDoc).ToList();
			}

			private List<SpotifyObjectDoc> Search(string query, SearchType type)
			{
				string response = String.Empty;
				try
				{
					switch (type)
					{
						case SearchType.ALBUM:
							response = _webClient.DownloadString(baseURL + "album?q=" + query);
							break;
						case SearchType.ARTIST:
							response = _webClient.DownloadString(baseURL + "artist?q=" + query);
							break;
						case SearchType.TRACK:
							response = _webClient.DownloadString(baseURL + "track?q=" + query);
							break;
					}
				}
				catch (Exception z)
				{
					return null;
				}

				XmlReader reader = XmlReader.Create(new StringReader(response));
				List<SpotifyObjectDoc> result = new List<SpotifyObjectDoc>();
				SpotifyObjectDoc doc;
				switch (type)
				{
					case SearchType.ARTIST:
						while ((doc = ReadArtist(reader)) != null)
						{
							result.Add(doc);
						}
						break;
					case SearchType.ALBUM:
						while ((doc = ReadAlbum(reader)) != null)
						{
							result.Add(doc);
						}
						break;
					case SearchType.TRACK:
						while ((doc = ReadTrack(reader)) != null)
						{
							result.Add(doc);
						}
						break;
				}
				return result;
			}
			#endregion

			#region Parse XML
			private TrackDoc ReadTrack(XmlReader reader)
			{
				if (reader.ReadToFollowing(SearchType.TRACK.ToString().ToLower()) && reader.MoveToFirstAttribute())
				{
					TrackDoc doc = new TrackDoc();
					doc.url = reader.Value;

					reader.ReadToFollowing("name");
					doc.name = reader.ReadElementContentAsString();

					doc.artist = ReadArtist(reader);
					doc.album = ReadAlbum(reader);
					doc.album.artist = doc.artist;
					return doc;
				}
				return null;
			}
			private AlbumDoc ReadAlbum(XmlReader reader)
			{
				if (reader.ReadToFollowing(SearchType.ARTIST.ToString().ToLower()) && reader.MoveToFirstAttribute())
				{
					AlbumDoc doc = new AlbumDoc();

					doc.url = reader.Value;
					reader.ReadToFollowing("name");
					doc.name = reader.ReadElementContentAsString();

					doc.artist = ReadArtist(reader);

					return doc;
				}
				return null;
			}
			private ArtistDoc ReadArtist(XmlReader reader)
			{
				if (reader.ReadToFollowing(SearchType.ARTIST.ToString().ToLower()) && reader.MoveToFirstAttribute())
				{
					ArtistDoc doc = new ArtistDoc();

					doc.url = reader.Value;
					reader.ReadToFollowing("name");
					doc.name = reader.ReadElementContentAsString();
					return doc;
				}
				return null;
			}
			#endregion

		}

		[DataContract]
		internal class SpotifyCommandJSON
		{
			[DataMember] public string command;
			[DataMember] public string uri;
			[DataMember] public int volume;
		}

		static void Main(string[] args)
		{
			SpotifyControl control = new SpotifyControl();
			control.Start();

			var ip = IPAddress.Parse("127.0.0.1");
			TcpListener server = new TcpListener(ip, 8012);
			server.Start();

			var client = server.AcceptSocket();

			byte [] data = new byte[1000];
			while (true)
			{
				client.Receive(data);
				var jsonString = Encoding.Default.GetString(data);

				var jsonDeserializer = new DataContractJsonSerializer(typeof(List<SpotifyCommandJSON>));

				using (var stream = new MemoryStream(Encoding.UTF8.GetBytes(jsonString)))
				{
					var spotifyCommand = jsonDeserializer.ReadObject(stream) as List<SpotifyCommandJSON>;

					SpotifyControl.SpotifyAction command;
					SpotifyControl.SpotifyAction.TryParse(spotifyCommand[0].command, out command);

					switch (command)
					{
						case SpotifyControl.SpotifyAction.VolumeUp:
							control.VolumeUp();
							break;
						case SpotifyControl.SpotifyAction.VolumeDown:
							control.VolumeDown();
							break;
						case SpotifyControl.SpotifyAction.Stop:
							control.Stop();
							break;
						case SpotifyControl.SpotifyAction.SetVolume:
							control.SetVolume(spotifyCommand[0].volume);
							break;
						case SpotifyControl.SpotifyAction.NextTrack:
							control.Next();
							break;
						case SpotifyControl.SpotifyAction.PreviousTrack:
							control.Previous();
							break;
						case SpotifyControl.SpotifyAction.Play:
							control.Play(spotifyCommand[0].uri);
							break;
					}

					Console.WriteLine(control.Status());	
				}
			}
			
			//while (true)
			//{
			//    var line = Console.ReadLine();
			//    switch (line)
			//    {
			//        case "Play":
			//        case "Pause":
			//            Win32.SendMessage(hwnd, Win32.Constants.WM_APPCOMMAND, IntPtr.Zero, new IntPtr((long)SpotifyAction.PlayPause));
			//            break;
			//        case "Next":
			//            Win32.SendMessage(hwnd, Win32.Constants.WM_APPCOMMAND, IntPtr.Zero, new IntPtr((long)SpotifyAction.NextTrack));
			//            break;
			//        case "Previous":
			//            Win32.SendMessage(hwnd, Win32.Constants.WM_APPCOMMAND, IntPtr.Zero, new IntPtr((long)SpotifyAction.PreviousTrack));
			//            break;
			//        case "Up":
			//            Win32.SendMessage(hwnd, Win32.Constants.WM_APPCOMMAND, IntPtr.Zero, new IntPtr((long)SpotifyAction.VolumeUp));
			//            break;
			//        case "Down":
			//            Win32.SendMessage(hwnd, Win32.Constants.WM_APPCOMMAND, IntPtr.Zero, new IntPtr((long)SpotifyAction.VolumeDown));
			//            break;
			//        default:
			//            Console.WriteLine("Did not recognize that command");
			//            break;
			//    }	
			//}
		}
	}
}
