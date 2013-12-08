using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.ComponentModel;
using GalaSoft.MvvmLight.Command;
using System.Net;
using System.Net.Sockets;
using System.Runtime.Serialization.Json;
using System.IO;
using System.Threading;
using SpotifyServer.Core;
using System.Runtime.Serialization;

namespace SpotifyServer
{
    public class StartAndStatusViewModel : INotifyPropertyChanged
    {

        [DataContract]
        public class SpotifyCommandJSON
        {
            [DataMember]
            public string command;
            [DataMember]
            public string uri;
            [DataMember]
            public int volume;
        }

        private int _port = 8012;

        private string _status;
        public string Status 
        {
            get { return _status; }
            set { _status = value; OnChanged("Status"); } 
        }
        public RelayCommand StartCommand { get; set; }
        
        private SpotifyServer.Core.SpotifyControl _control;
               
        public StartAndStatusViewModel()
        {
            StartCommand = new RelayCommand(StartCommandHandler);
         
            _control = new SpotifyServer.Core.SpotifyControl();
        }

        private void StartCommandHandler()
        {
            _control.Start();

            Thread serverThread = new Thread(new ThreadStart(ServerThread));
            serverThread.Start();

            Thread broadcastThread = new Thread(new ThreadStart(BroadcastThread));
            broadcastThread.Start();
        }

        private void BroadcastThread()
        {
            Socket discovery = new Socket(AddressFamily.InterNetwork, SocketType.Dgram, ProtocolType.Udp);
            IPAddress broadcastAddr = IPAddress.Parse("192.168.1.255");
            IPEndPoint endPoint = new IPEndPoint(broadcastAddr, 8432);

            while (true)
            {
                Thread.Sleep(500);

                Status = _control.Status().ToString();

                var broadcastMsg = Encoding.UTF8.GetBytes("{" + String.Format("\"port\":\"{0}\", \"status\" : {1}", _port, _control.GetJSONStatus()) + "}");
                discovery.SendTo(broadcastMsg, endPoint);
            }
        }

        private void ServerThread()
        {
            var ip = IPAddress.Parse("127.0.0.1");
            TcpListener server = new TcpListener(ip, _port);
            server.Start();

            while (true)
            {
                var client = server.AcceptSocket();

                Thread clientThread = new Thread(new ThreadStart(() => ClientThread(client)));
                clientThread.Start();
            }
        }

        private void ClientThread(Socket client)
        {
            try
            {
                byte[] data = new byte[1000];
                while (true)
                {
                    int count = client.Receive(data);
                    var jsonString = Encoding.Default.GetString(data).Substring(0, count);
                    var jsonDeserializer = new DataContractJsonSerializer(typeof(List<SpotifyCommandJSON>));

                    using (var stream = new MemoryStream(Encoding.UTF8.GetBytes(jsonString)))
                    {
                        var spotifyCommand = jsonDeserializer.ReadObject(stream) as List<SpotifyCommandJSON>;
                    
                        var cmd = spotifyCommand[0].command;
                    
                        if (cmd == SpotifyControl.SpotifyAction.VolumeUp.ToString())
                        {
                            _control.VolumeUp();
                        } else if (cmd == SpotifyControl.SpotifyAction.VolumeDown.ToString())
                        {
                            _control.VolumeDown();
                        } else if (cmd == SpotifyControl.SpotifyAction.Stop.ToString())
                        {
                            _control.Stop();
                        } else if (cmd == SpotifyControl.SpotifyAction.SetVolume.ToString())
                        {
                            _control.SetVolume(spotifyCommand[0].volume);
                        } else if (cmd == SpotifyControl.SpotifyAction.NextTrack.ToString())
                        {
                            _control.Next();
                        } else if (cmd == SpotifyControl.SpotifyAction.PreviousTrack.ToString())
                        {
                            _control.Previous();
                        } else if (cmd == SpotifyControl.SpotifyAction.PreviousTrack.ToString())
                        {
                            _control.Play(spotifyCommand[0].uri);
                        }
                        else if (cmd == SpotifyControl.SpotifyAction.Status.ToString())
                        {
                            var status = _control.GetJSONStatus();
                            client.Send(Encoding.UTF8.GetBytes(status));
                        }
                    }
                 }
            }
            catch (Exception e)
            {
                return;
            }
        }
#region INotifyPropertyChanged
        public event PropertyChangedEventHandler PropertyChanged;

        private void OnChanged(String propertyName = "")
        {
            if (PropertyChanged != null)
            {
                PropertyChanged(this, new PropertyChangedEventArgs(propertyName));
            }
        }
        #endregion
    }
}
