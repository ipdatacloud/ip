package com.ipdatacloud.ipdatacloud;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.util.*;

public class Ip {

    private int[] prefStart = new int[256];
    private int[] prefEnd = new int[256];
    private long[] endArr;
    private String[] addrArr;

    private static Ip instance = null;

    private Ip() {
        Path path = Paths.get("/home/dream/Desktop/ipdatacloud.dat");

        byte[] data = null;
        try {
            data = Files.readAllBytes(path);
        } catch (IOException e) {
            e.printStackTrace();
        }

        for (int k = 0; k < 256; k++) {
            int i = k * 8 + 4;
            prefStart[k] = (int) UnpackInt4byte(data[i], data[i + 1], data[i + 2], data[i + 3]);
            prefEnd[k] = (int) UnpackInt4byte(data[i + 4], data[i + 5], data[i + 6], data[i + 7]);

        }

        int RecordSize = (int) UnpackInt4byte(data[0], data[1], data[2], data[3]);
        endArr = new long[RecordSize];
        addrArr = new String[RecordSize];
        for (int i = 0; i < RecordSize; i++) {
            int p = 2052 + (i * 9);
            long endipnum = UnpackInt4byte(data[p], data[1 + p], data[2 + p], data[3 + p]);

            int offset = (int)UnpackInt4byte(data[4 + p], data[5 + p], data[6 + p],data[7+p]);
            int length = data[8 + p] & 0xff;

            endArr[i] = endipnum;

            addrArr[i] = new String(Arrays.copyOfRange(data,  offset, (offset + length)));
        }

    }

    public static synchronized Ip getInstance() {
        if (instance == null)
            instance = new Ip();
        return instance;
    }

    public String Get(String ip) {

        String[] ips = ip.split("\\.");
        int pref = Integer.valueOf(ips[0]);
        long val = ipToLong(ip);
        int low = prefStart[pref], high = prefEnd[pref];
        long cur = low == high ? low : Search(low, high, val);
        return addrArr[(int) cur];

    }

    private int Search(int low, int high, long k) {
        int M = 0;
        while (low <= high) {
            int mid = (low + high) / 2;

            long endipNum = endArr[mid];
            if (endipNum >= k) {
                M = mid;
                if (mid == 0) {
                    break;
                }
                high = mid - 1;
            } else
                low = mid + 1;
        }
        return M;
    }

    private long UnpackInt4byte(byte a, byte b, byte c, byte d) {
        return (a & 0xFFL) | ((b << 8) & 0xFF00L) | ((c << 16) & 0xFF0000L) | ((d << 24) & 0xFF000000L);

    }


    private long ipToLong(String ip) {
        long result = 0;
        String[] d = ip.split("\\.");
        for (String b : d) {
            result <<= 8;
            result |= Long.parseLong(b) & 0xff;
        }
        return result;
    }

    public static void main(String[] args) {

        Ip ip = Ip.getInstance();
        System.out.println(ip.Get("35.201.142.37"));

    }

}
