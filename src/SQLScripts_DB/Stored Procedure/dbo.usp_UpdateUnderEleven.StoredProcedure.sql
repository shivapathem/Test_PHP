USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_UpdateUnderEleven]    Script Date: 02/11/2023 20:20:46 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE  [dbo].[usp_UpdateUnderEleven]
@AllocationID                  INT,
@pNetLogin                     VARCHAR(30),
@pIsUnderElevenBreakOverride   BIT, 
@pOverrideUnderElevenHrs       INT,
@pUnderElevenComment           NVARCHAR(500),
@pRemoveOverride               INT = NULL

AS
BEGIN

    SET NOCOUNT ON

    SET DATEFORMAT YMD
	
    DECLARE @vIsUnderElevenBreakOverride   BIT 
    DECLARE @vOverrideUnderElevenHrs       INT
    DECLARE @vUnderElevenComment           NVARCHAR(500)
	DECLARE @vCalculatedUnderElevenHrs     INT
    DECLARE @vSQL                          VARCHAR(MAX)
	DECLARE @vHistory                      NVARCHAR(MAX)	
	DECLARE @updateflag                    INT = 0 
	DECLARE @vuserID                       INT	
	DECLARE @vname                         VARCHAR(100)
		
	BEGIN TRY

	   SELECT  @vname =  CASE WHEN (sp.DisplayName IS NULL) 
	                         THEN CASE WHEN (sd.PreferredForename IS NULL or sd.PreferredForename = '')
                                       THEN (sd.Forename + ' ' + sd.Surname) 
									   ELSE (sd.PreferredForename + ' ' + sd.Surname)    END
                             ELSE sp.DisplayName END 
	    FROM StaffDetails sd (nolock)
		LEFT JOIN ScheduledPeople sp (nolock) on sp.StaffDetailsID = sd.StaffID
	   WHERE sd.NetLogin=@pNetLogin

	   SELECT @vuserID = UserID 
		 FROM [dbo].[Users] 
		WHERE NetLogin = @pNetLogin  			
 
	  SELECT @vIsUnderElevenBreakOverride = IsUnderElevenBreakOverride,
	         @vOverrideUnderElevenHrs     = OverrideUnderElevenHrs,
			 @vUnderElevenComment         = UnderElevenComment,
			 @vCalculatedUnderElevenHrs   = CalculatedUnderElevenHrs
	    FROM Allocations 
	   WHERE ID = @AllocationID	   	  

	  SET @vSQL = 'UPDATE Allocations SET '
	  SET @vHistory = ''
	  
      IF ( ISNULL(@pRemoveOverride,0) = 1 )
	   BEGIN
		SET @pIsUnderElevenBreakOverride = 0
		SET @pOverrideUnderElevenHrs = 0
		SET @vHistory = 'Override For Under 11 was removed by '+@vname+' on ' 
								   + FORMAT(Getdate(),'dd/MM/yyyy')+' at '
								   + FORMAT(Getdate(),'HH:mm')+'.'		
	   END

	  IF ( ISNULL(@vIsUnderElevenBreakOverride,0) <> ISNULL(@pIsUnderElevenBreakOverride,0)  ) 
	   BEGIN 
		 SET @updateflag = 1	
		 SET @vSQL = @vSQL+' IsUnderElevenBreakOverride = '+cast(@pIsUnderElevenBreakOverride as varchar)+', '	
		 
		  IF ( ISNULL(@pRemoveOverride,0) <> 1 )
		   SET @vHistory = 'Under11 Overridden’ by '+@vname+' on ' 
		                   + FORMAT(Getdate(),'dd/MM/yyyy')+' at '
						   + FORMAT(Getdate(),'HH:mm')+'.'		   
		  
	   END	
	   
	  IF ( ISNULL(@vOverrideUnderElevenHrs,0) <> ISNULL(@pOverrideUnderElevenHrs,0)  ) 
	   BEGIN    
		 SET @vSQL = @vSQL+' OverrideUnderElevenHrs = '+cast(@pOverrideUnderElevenHrs as varchar)+', '	
		 
		  IF ( ISNULL(@pRemoveOverride,0) <> 1 )
		   BEGIN
		    SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
		    SET @vHistory = @vHistory+'Actual Under 11 Hours changed from ['
						 + right('0' + CAST( isnull(@vOverrideUnderElevenHrs,0) / 3600 AS varchar(2)),2) + '.'  
						 + LEFT( substring(CAST(round(cast((isnull(@vOverrideUnderElevenHrs,0) % 3600) as float)/3600 ,2) AS varchar),3,2)+'00',2)+'] To ['
						 + right('0' + CAST( isnull(@pOverrideUnderElevenHrs,0) / 3600 AS varchar(2)),2) + '.'  
						 + LEFT(substring(CAST(round(cast((isnull(@pOverrideUnderElevenHrs,0) % 3600) as float)/3600 ,2) AS varchar),3,2)+'00',2)+'] by ' 		 
  		                 +@vname+' On ' + FORMAT(Getdate(),'dd/MM/yyyy')+' at '+FORMAT(Getdate(),'HH:mm')+'.'
		   END
		 SET @updateflag = 1			 
	   END	  		

	  IF ( ISNULL(@vUnderElevenComment,'X') <> ISNULL(@pUnderElevenComment,'X')  ) 
	   BEGIN 	   
		 SET @vSQL = @vSQL+' UnderElevenComment = '''+@pUnderElevenComment+''', '	
		 SET @updateflag = 1			 
	   END	  
	      
      IF ( @updateflag = 1)
	   BEGIN

		 SET @vSQL = @vSQL+' UpdatedBy = '+ CAST(@vuserID AS VARCHAR)
						                  +', UpdatedDate = getutcdate()
							  WHERE ID = '+cast(@AllocationID as varchar)
		 EXEC (@vSQL)		 

			INSERT INTO history ( historytype,
								  attributeid,
								  HistorySubType,
								  datetime,
								  userid,
								  history )		
			SELECT ht.id AS historytype,
				   @AllocationID AS attributeid,
				   'PH' AS HistorySubType,
				   getdate(),
				   @vuserID,
				   @vHistory
			  FROM HistoryTypes HT
			 WHERE historytype = 'AllocationDuty'
			 
		UPDATE AL 
		   SET AL.IsUnderElevenBreakOverride = TAL.IsUnderElevenBreakOverride,
		       AL.OverrideUnderElevenHrs = TAL.OverrideUnderElevenHrs,
			   AL.UnderElevenComment = TAL.UnderElevenComment,
			   AL.UpdatedBy = @vuserID,
			   AL.UpdatedDate = getutcdate()
		  FROM Allocations AL
		 INNER JOIN Allocations TAL ON AL.SchedulingPersonID = TAL.SchedulingPersonID
		                           AND AL.WeekNumber = TAL.WeekNumber
								   AND AL.iDay = TAL.iDay
		 WHERE TAL.ID = @AllocationID
		   AND AL.ID <> @AllocationID

	   END	   
	   
		SELECT 0 AS RETURNVAL
			
	END TRY
				
	BEGIN CATCH
			
		SELECT 1 AS RETURNVAL
			
	END CATCH;			
END	