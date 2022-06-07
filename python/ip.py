import mmap
import struct
import socket

class Ip:
    def __init__(self, file_name):
        self._handle = open(file_name, "rb")
        self.data = mmap.mmap(self._handle.fileno(), 0, access=mmap.ACCESS_READ)
        self.prefArr =[]
        record_size = self.unpack_int_4byte(0)
        i = 0
        while i < 256:
            p = i * 8 + 4 
            self.prefArr.append([self.unpack_int_4byte(p),self.unpack_int_4byte(p+4)])          
            i += 1

        self.endArr = []
        self.addrArr = []
        j = 0
        while j < record_size:
            p = 2052 + (j * 9)
            offset = self.unpack_int_4byte(4 + p)  
            length = self.unpack_int_1byte(8 + p)
            self.endArr.append(self.unpack_int_4byte(p))
            self.addrArr.append(self.data[offset:offset + length].decode('utf-8'))
            j += 1

    def __enter__(self):
        return self

    def __exit__(self, exc_type, exc_value, exc_tb):
        self.close()

    def close(self):
        self._handle.close()

    def get(self, ip):
        ipdot = ip.split('.')
        prefix = int(ipdot[0])
        if prefix < 0 or prefix > 255 or len(ipdot) != 4:
            raise ValueError("invalid ip address")
        intIP = self.ip_to_int(ip)
        low = self.prefArr[prefix][0]
        high = self.prefArr[prefix][1]
        cur, end_ip_num =low if low == high else self.search(low, high, intIP)
        return self.addrArr[cur]
        
            

    def search(self, low, high, k):
        print(self.endArr[:10])
        M = 0
        end_ip_num = 0
        while low <= high:
            mid = (low + high) // 2
            end_ip_num = self.endArr[mid]
            if end_ip_num >= k:
                M = mid
                if mid == 0:
                    break
                high = mid - 1
            else:
                low = mid + 1

        return M, end_ip_num

    def ip_to_int(self, ip):
        _ip = socket.inet_aton(ip)
        return struct.unpack("!L", _ip)[0]

    def unpack_int_4byte(self, offset):
        return struct.unpack('<L', self.data[offset:offset + 4])[0]

    def unpack_int_1byte(self, offset):
        return struct.unpack('B', self.data[offset:offset + 1])[0]
