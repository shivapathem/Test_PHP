USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_Import_AL_A7_StaffDetails]    Script Date: 08/10/2025 19:23:28 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER     PROCEDURE [dbo].[usp_Import_AL_A7_StaffDetails] 
	
AS
BEGIN

DECLARE @strUpdateDateTime	VARCHAR(35)
DECLARE @TmpHistory			VARCHAR(4000)

BEGIN TRY


    SET @strUpdateDateTime = CONVERT(VARCHAR, GETDATE(), 106) + ' at ' + CONVERT(VARCHAR, GETDATE(), 108)
	SET @Tmphistory = 'Updated by AutoUpdate on ' + @strUpdateDateTime

	UPDATE T1
	   SET  T1.StaffNumber = T2.StaffNumber,
			T1.Surname = T2.Surname,
			T1.Forename = T2.Forename,
			T1.Initials = T2.Initials,			
			T1.Title = T2.Title,			
			T1.LeaveDate = T2.LeaveDate,
			T1.LeaveDateSat = T2.LeaveDateSat,
			T1.LastModDate = T2.LastModDate,
		    T1.Room = T2.Room,
			T1.SubLocation = T2.SubLocation,
			T1.BuildingCode = T2.BuildingCode,
			T1.OfficeExtension = T2.OfficeExtension,
			T1.OfficeMobile = T2.OfficeMobile,
			T1.PreferredForename = T2.PreferredForename,
			T1.NetLogin = T2.NetLogin,
			T1.InternalEmail = T2.InternalEmail,
			T1.LastModBy = 'AutoUpdate',
			T1.ManualEntry = 0,
			T1.History=CASE WHEN (NULLIF(T1.History, '') IS NULL) Then @TmpHistory
						   ELSE T1.History + CHAR(13) + CHAR(10) + @TmpHistory
					  END,	 
			
			LastModChanges = 'Latest Changes :' +
							 CASE WHEN ((T1.Surname <> T2.Surname) OR (T1.Surname IS NULL AND T2.Surname IS NOT NULL) OR (T1.Surname IS NOT NULL AND T2.Surname IS NULL)) THEN CHAR(13) + CHAR(10) + ' -- Surname changed from ' + ISNULL(T1.Surname, 'Nothing') + ' to ' + ISNULL(T2.Surname, 'Nothing')
							      ELSE ''
						     END +
						     CASE WHEN ((T1.Forename <> T2.Forename) OR (T1.Forename IS NULL AND T2.Forename IS NOT NULL) OR (T1.Forename IS NOT NULL AND T2.Forename IS NULL)) THEN CHAR(13) + CHAR(10) + ' -- Forename changed from ' + ISNULL(T1.Forename, 'Nothing') + ' to ' + ISNULL(T2.Forename, 'Nothing')
							      ELSE ''
						     END +
						     CASE WHEN ((T1.Initials <> T2.Initials) OR (T1.Initials IS NULL AND T2.Initials IS NOT NULL) OR (T1.Initials IS NOT NULL AND T2.Initials IS NULL)) THEN CHAR(13) + CHAR(10) + ' -- Initials changed from ' + ISNULL(T1.Initials, 'Nothing') + ' to ' + ISNULL(T2.Initials, 'Nothing')
							      ELSE ''
						     END +
						     CASE WHEN ((T1.Title <> T2.Title) OR (T1.Title IS NULL AND T2.Title IS NOT NULL) OR (T1.Title IS NOT NULL AND T2.Title IS NULL)) THEN CHAR(13) + CHAR(10) + ' -- Title changed from ' + ISNULL(T1.Title, 'Nothing') + ' to ' + ISNULL(T2.Title, 'Nothing')
							      ELSE ''
						     END +
						    
							 CASE WHEN ((T1.LeaveDate <> T2.LeaveDate) OR (T1.LeaveDate IS NULL AND T2.LeaveDate IS NOT NULL) OR (T1.LeaveDate IS NOT NULL AND T2.LeaveDate IS NULL)) THEN CHAR(13) + CHAR(10) + ' -- Leave Date changed from ' + ISNULL(CONVERT(VARCHAR(10), T1.LeaveDate, 103), 'Nothing') + ' to ' + ISNULL(CONVERT(VARCHAR(10), T2.LeaveDate, 103), 'Nothing')
								  ELSE ''
							 END +
							 CASE WHEN ((T1.Netlogin <> T2.Netlogin) OR (T1.Netlogin IS NULL AND T2.Netlogin IS NOT NULL) OR (T1.Netlogin IS NOT NULL AND T2.Netlogin IS NULL)) THEN CHAR(13) + CHAR(10) + ' -- Network Login changed from ' + ISNULL(T1.Netlogin, 'Nothing') + ' to ' + ISNULL(T2.Netlogin, 'Nothing')
											 ELSE ''
							 END +
							 CASE WHEN ((T1.InternalEmail <> T2.InternalEmail) OR (T1.InternalEmail IS NULL AND T2.InternalEmail IS NOT NULL) OR (T1.InternalEmail IS NOT NULL AND T2.InternalEmail IS NULL)) THEN CHAR(13) + CHAR(10) + ' -- Email Address changed from ' + ISNULL(T1.InternalEmail, 'Nothing') + ' to ' + ISNULL(T2.InternalEmail, 'Nothing')
									 ELSE ''
						     END,
			CriticalLevel = CASE WHEN ((T1.LeaveDate <> T2.LeaveDate) OR (T1.LeaveDate IS NULL AND T2.LeaveDate IS NOT NULL) OR (T1.LeaveDate IS NOT NULL AND T2.LeaveDate IS NULL)) THEN 2
							     ELSE 1
						    END
	  FROM  BBCSchedules.dbo.StaffDetails T1 INNER JOIN [AllocateLink].[dbo].[TP_A7_StaffDetails] T2 ON T2.StaffID = T1.StaffID
	 WHERE (((T1.LastModDate <> T2.LastModDate) OR (T1.LastModDate IS NULL AND T2.LastModDate IS NOT NULL) OR (T1.LastModDate IS NOT NULL AND T2.LastModDate IS NULL))
	    OR  ((T1.Surname <> T2.Surname) OR (T1.Surname IS NULL AND T2.Surname IS NOT NULL) OR (T1.Surname IS NOT NULL AND T2.Surname IS NULL))
		OR	((T1.Forename <> T2.Forename) OR (T1.Forename IS NULL AND T2.Forename IS NOT NULL) OR (T1.Forename IS NOT NULL AND T2.Forename IS NULL))
		OR  ((T1.Initials <> T2.Initials) OR (T1.Initials IS NULL AND T2.Initials IS NOT NULL) OR (T1.Initials IS NOT NULL AND T2.Initials IS NULL))
		OR  ((T1.Title <> T2.Title) OR (T1.Title IS NULL AND T2.Title IS NOT NULL) OR (T1.Title IS NOT NULL AND T2.Title IS NULL))
		OR	((T1.LeaveDate <> T2.LeaveDate) OR (T1.LeaveDate IS NULL AND T2.LeaveDate IS NOT NULL) OR (T1.LeaveDate IS NOT NULL AND T2.LeaveDate IS NULL))
		OR ((T1.Room <> T2.Room) OR (T1.Room IS NULL AND T2.Room IS NOT NULL) OR (T1.Room IS NOT NULL AND T2.Room IS NULL))
		OR ((T1.SubLocation <> T2.SubLocation) OR (T1.SubLocation IS NULL AND T2.SubLocation IS NOT NULL) OR (T1.SubLocation IS NOT NULL AND T2.SubLocation IS NULL))
		OR ((T1.BuildingCode <> T2.BuildingCode) OR (T1.BuildingCode IS NULL AND T2.BuildingCode IS NOT NULL) OR (T1.BuildingCode IS NOT NULL AND T2.BuildingCode IS NULL))
		OR ((T1.OfficeExtension <> T2.OfficeExtension) OR (T1.OfficeExtension IS NULL AND T2.OfficeExtension IS NOT NULL) OR (T1.OfficeExtension IS NOT NULL AND T2.OfficeExtension IS NULL))
		OR ((T1.OfficeMobile <> T2.OfficeMobile) OR (T1.OfficeMobile IS NULL AND T2.OfficeMobile IS NOT NULL) OR (T1.OfficeMobile IS NOT NULL AND T2.OfficeMobile IS NULL))
		OR  ((T1.NetLogin <> T2.NetLogin) OR (T1.NetLogin IS NULL AND T2.NetLogin IS NOT NULL) OR (T1.NetLogin IS NOT NULL AND T2.NetLogin IS NULL))
		OR ((T1.InternalEmail <> T2.InternalEmail) OR (T1.InternalEmail IS NULL AND T2.InternalEmail IS NOT NULL) OR (T1.InternalEmail IS NOT NULL AND T2.InternalEmail IS NULL)))
		



	INSERT INTO BBCSchedules.dbo.StaffDetails(StaffID, EmpNumber, StaffNumber, Surname, Forename, SecondName, Initials, Title,Room,SubLocation ,BuildingCode,OfficeExtension,OfficeMobile,NetLogin,InternalEmail, IsLeaver,LeaveDate, LeaveDateSat, CreatedDate, LastModDate, LastModBy, ManualEntry, 
								 History,  CriticalLevel)
	SELECT DISTINCT StaffID, EmpNumber, StaffNumber, Surname, Forename, SecondName, Initials, Title,Room,SubLocation ,BuildingCode,OfficeExtension,OfficeMobile,NetLogin,InternalEmail,IsLeaver, LeaveDate, LeaveDateSat, Getdate(), Getdate(), 'AutoUpdate', 0, History, 2
	  FROM [AllocateLink].[dbo].[TP_A7_StaffDetails]
	 WHERE StaffID NOT IN (SELECT T1.StaffID FROM BBCSchedules.dbo.StaffDetails T1)	
	  

END TRY
BEGIN CATCH

ROLLBACK TRANSACTION

	INSERT INTO ErrorLog
			VALUES
			(
			ERROR_NUMBER(),
			ERROR_STATE(),
			ERROR_SEVERITY(),
			ERROR_LINE(),
			'usp_Import_AL_A7_StaffDetails',
			ERROR_MESSAGE(),
			GETDATE(),
			0
			)
END CATCH
END