using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Net.Sockets;

namespace SpotifyClientTester
{
    class Program
    {
        static void Main(string[] args)
        {
            TcpClient client = new TcpClient("localhost", 8012);

            string command = "[{\"command\":\"NextTrack\"}]";

            client.GetStream().Write(Encoding.UTF8.GetBytes(command),0,command.Length);
        }
    }
}
