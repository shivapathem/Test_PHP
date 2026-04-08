USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_GET_ScheduledPeopleStaffDetails]    Script Date: 18/08/2022 17:22:54 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE [dbo].[usp_Import_TP_A7_Delete_StaffConfig]
       @SystemID            INT,
       @StartDate           DATETIME,
       @EndDate             DATETIME
AS
-- =============================================
-- Author:      Michael Hoskin
-- Create date: 08/09/2022
-- Description: SP to mark the Allocate7 records as not Active.
-- =============================================
BEGIN
        SET NOCOUNT ON;


              
              -- Update End Date =  the previous record should be set to the end date of the one being deleted (Staff Config)
              
              SELECT A7SC.IsActive as A7IsActive,TPSC.IsActive as TPIsActive, A7SC.StartDate, A7SC.EndDate, A7SC.StaffID INTO #ConfigDeleted
              FROM Allocate7.dbo.StaffConfig A7SC JOIN Allocate7.dbo.StaffDetails A7SD ON A7SD.StaffID=A7SC.StaffID
              JOIN AllocateLink.dbo.TP_A7_StaffDetails TPSD ON TPSD.NetLogin=A7SD.NetLogin
              JOIN AllocateLink.dbo.TP_A7_StaffConfig TPSC ON TPSD.StaffID=TPSC.StaffID
              AND A7SC.StartDate=TPSC.StartDate --AND A7SC.EndDate=TPSC.EndDate
                        WHERE A7SC.IsActive<>TPSC.IsActive

              UPDATE SC
              SET SC.EndDate=D.EndDate 
              FROM Allocate7.dbo.StaffConfig SC JOIN #ConfigDeleted D ON SC.StaffID=D.StaffID
              WHERE SC.StartDate = (Select max(T1.Startdate) 
                                                      from Allocate7.dbo.StaffConfig T1 JOIN #ConfigDeleted T2 ON T1.StaffID=T2.StaffID 
                                                     where T1.StartDate<T2.StartDate)


              -- Update End Date =  the previous record should be set to the end date of the one being deleted (Staff Contract)

              SELECT A7SC.IsActive as A7IsActive,TPSC.IsActive as TPIsActive, A7SC.StartDate, A7SC.EndDate, A7SC.StaffID INTO #ContractDeleted
              FROM Allocate7.dbo.StaffContract A7SC JOIN Allocate7.dbo.StaffDetails A7SD ON A7SD.StaffID=A7SC.StaffID
                               JOIN AllocateLink.dbo.TP_A7_StaffDetails TPSD ON TPSD.NetLogin=A7SD.NetLogin
                               JOIN AllocateLink.dbo.TP_A7_StaffContract TPSC ON TPSD.StaffID=TPSC.StaffID
                               AND A7SC.StartDate=TPSC.StartDate --AND A7SC.EndDate=TPSC.EndDate
                               WHERE A7SC.IsActive<>TPSC.IsActive
              
              UPDATE SC
              SET SC.EndDate=D.EndDate 
              FROM Allocate7.dbo.StaffConfig SC JOIN #ConfigDeleted D ON SC.StaffID=D.StaffID
              WHERE SC.StartDate = (Select max(T1.Startdate) 
                                                          from Allocate7.dbo.StaffConfig T1 JOIN #ContractDeleted T2 ON T1.StaffID=T2.StaffID 
                                                     where T1.StartDate<T2.StartDate)

       -------------- Update IsActive : StaffConfig and StaffContract


       Update A7SC
              SET A7SC.IsActive=TPSC.IsActive
              FROM Allocate7.dbo.StaffConfig A7SC JOIN Allocate7.dbo.StaffDetails A7SD ON A7SD.StaffID=A7SC.StaffID
              JOIN AllocateLink.dbo.TP_A7_StaffDetails TPSD ON TPSD.NetLogin=A7SD.NetLogin
              JOIN AllocateLink.dbo.TP_A7_StaffConfig TPSC ON TPSD.StaffID=TPSC.StaffID
              AND A7SC.StartDate=TPSC.StartDate --AND A7SC.EndDate=TPSC.EndDate
                        WHERE A7SC.IsActive<>TPSC.IsActive AND A7SC.ConfigID=TPSC.ConfigID


       Update A7SC
              SET A7SC.IsActive=TPSC.IsActive
             FROM Allocate7.dbo.StaffContract A7SC JOIN Allocate7.dbo.StaffDetails A7SD ON A7SD.StaffID=A7SC.StaffID
              JOIN AllocateLink.dbo.TP_A7_StaffDetails TPSD ON TPSD.NetLogin=A7SD.NetLogin
              JOIN AllocateLink.dbo.TP_A7_StaffContract TPSC ON TPSD.StaffID=TPSC.StaffID
              AND A7SC.StartDate=TPSC.StartDate --AND A7SC.EndDate=TPSC.EndDate
                        WHERE A7SC.IsActive<>TPSC.IsActive AND A7SC.ContractID=TPSC.ContractID

END